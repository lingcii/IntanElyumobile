/**
 * MUNICIPAL Fare Data API
 * Role: *_mto (Municipal Tourism Offices)
 * Permissions: View all, Upload, Activate own guides
 * No delete permission
 */

const API_BASE = window.API_CONFIG?.MUNICIPAL ? (window.API_CONFIG.MUNICIPAL + '/fare-data') : 'http://' + (window.location.hostname || '127.0.0.1') + ':8000/api/municipal/fare-data';

// Map legacy action strings to Laravel REST paths
function muniApiUrl(action, params = {}) {
    const map = {
        'get_fare_guides':        `${API_BASE}/guides`,
        'get_fare_matrices':      `${API_BASE}/matrices`,
        'get_uploads':            `${API_BASE}/uploads`,
        'get_import_logs':        `${API_BASE}/import-logs`,
        'get_validation_errors':  `${API_BASE}/validation-errors`,
        'upload_pdf':             `${API_BASE}/upload`,
        'sync_fare_guide':        `${API_BASE}/sync`,
    };
    const base = map[action] || `${API_BASE}/${action.replace(/_/g, '-')}`;
    const allParams = { ...params, _t: Date.now() };
    const qs   = '?' + new URLSearchParams(allParams).toString();
    return base + qs;
}

document.addEventListener('DOMContentLoaded', function () {
    initUploadArea();
    loadFareGuides();
    loadUploadHistory();
});

// ── Upload Area ───────────────────────────────────────────────────────────────

function initUploadArea() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput  = document.getElementById('fareFileInput');

    uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        uploadArea.style.background  = 'rgba(0, 113, 197, 0.1)';
        uploadArea.style.borderColor = 'var(--lupto-primary)';
    });
    uploadArea.addEventListener('dragleave', function (e) {
        e.preventDefault();
        uploadArea.style.background  = '';
        uploadArea.style.borderColor = '';
    });
    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        uploadArea.style.background  = '';
        uploadArea.style.borderColor = '';
        if (e.dataTransfer.files.length > 0) handleFileUpload(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', function (e) {
        if (e.target.files.length > 0) handleFileUpload(e.target.files[0]);
    });
}

async function handleFileUpload(file) {
    if (!file.type.includes('pdf') && !file.name.toLowerCase().endsWith('.pdf')) {
        showResult('error', 'Please select a PDF file');
        return;
    }

    const formData = new FormData();
    formData.append('pdf_file', file);
    showProgress(true, 'Uploading...');

    try {
        const response = await fetch(muniApiUrl('upload_pdf'), {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        const result = await response.json();
        showProgress(false);

        if (result.success) {
            let html = `
                <div style="background:#d4edda;color:#155724;padding:16px;border-radius:8px;">
                    <h4 style="margin:0 0 8px;"><i class="fas fa-check-circle"></i> Upload Successful!</h4>
                    <p style="margin:0 0 8px;">Total Records: ${result.total_records}</p>
                    <p style="margin:0 0 8px;">Valid Records: ${result.valid_records}</p>
            `;
            if (result.errors && result.errors.length > 0) {
                html += `<p style="margin:8px 0 0;color:#856404;">
                    <i class="fas fa-exclamation-triangle"></i> ${result.errors.length} validation error(s).
                    <button class="btn-gov btn-gov-secondary" style="margin-left:8px;padding:4px 8px;font-size:12px;" onclick="showValidationErrors(${result.upload_id})">View Errors</button>
                </p>`;
            }
            html += `</div>`;
            showResult('success', html);
            loadFareGuides();
            loadUploadHistory();
        } else {
            showResult('error', result.error || 'Upload failed');
        }
    } catch (err) {
        showProgress(false);
        showResult('error', 'Upload failed: ' + err.message);
    }
}

function showProgress(show, text = '') {
    const progressDiv  = document.getElementById('uploadProgress');
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    if (show) {
        progressDiv.style.display  = 'block';
        progressFill.style.width   = '50%';
        progressText.textContent   = text;
    } else {
        progressFill.style.width = '100%';
        setTimeout(() => { progressDiv.style.display = 'none'; progressFill.style.width = '0%'; }, 500);
    }
}

function showResult(type, message) {
    const resultDiv = document.getElementById('uploadResult');
    resultDiv.style.display = 'block';
    if (type === 'success') {
        resultDiv.innerHTML = message;
    } else {
        resultDiv.innerHTML = `
            <div style="background:#f8d7da;color:#721c24;padding:16px;border-radius:8px;">
                <h4 style="margin:0 0 8px;"><i class="fas fa-exclamation-circle"></i> Error</h4>
                <p style="margin:0;">${escapeHtml(message)}</p>
            </div>`;
    }
}

// ── Fare Guides ───────────────────────────────────────────────────────────────

async function loadFareGuides() {
    try {
        const response = await fetch(muniApiUrl('get_fare_guides'), { credentials: 'include' });
        const result   = await response.json();
        const tbody    = document.getElementById('fareGuidesBody');

        if (!result.fare_guides || result.fare_guides.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-muted);">
                <i class="fas fa-inbox" style="font-size:24px;margin-bottom:8px;display:block;"></i>
                No fare guides yet.
            </td></tr>`;
            return;
        }

        tbody.innerHTML = result.fare_guides.map(guide => {
            const statusStyle = guide.status === 'active'   ? 'background:#d4edda;color:#155724;' :
                                guide.status === 'archived' ? 'background:#e2e3e5;color:#383d41;' :
                                                              'background:#fff3cd;color:#856404;';
            // Show Activate only for guides this municipality uploaded (owns)
            const canActivate = guide.status !== 'active';

            return `
                <tr>
                    <td>${escapeHtml(guide.title)}</td>
                    <td>${escapeHtml(guide.vehicle_type.replace(/_/g, ' '))}</td>
                    <td>${escapeHtml(guide.region)}</td>
                    <td>${formatDate(guide.effective_date)}</td>
                    <td><span style="padding:4px 8px;border-radius:4px;font-size:12px;${statusStyle}">${escapeHtml(guide.status)}</span></td>
                    <td>${escapeHtml(guide.created_by_name)}</td>
                    <td style="position:relative;overflow:visible;">
                        <div class="dropdown" style="position:relative;display:inline-block;">
                            <button class="btn-gov btn-gov-secondary" style="padding:4px 8px;" onclick="toggleDropdown(event,${guide.id})">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu" id="dropdown-${guide.id}" style="display:none;position:absolute;right:0;top:100%;margin-top:4px;background:white;border:1px solid #e0e0e0;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.25);min-width:180px;z-index:9999;padding:4px 0;">
                                <button class="dropdown-item" style="width:100%;text-align:left;padding:10px 16px;border:none;background:none;cursor:pointer;display:flex;align-items:center;gap:8px;"
                                    onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='transparent'"
                                    onclick="viewFareMatrix(${guide.id}, true);closeAllDropdowns();">
                                    <i class="fas fa-eye"></i> View Fare Matrix
                                </button>
                                ${canActivate ? `
                                <button class="dropdown-item" style="width:100%;text-align:left;padding:10px 16px;border:none;background:none;cursor:pointer;display:flex;align-items:center;gap:8px;"
                                    onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='transparent'"
                                    onclick="syncFareGuide(${guide.id},'active');closeAllDropdowns();">
                                    <i class="fas fa-check-circle"></i> Activate
                                </button>` : ''}
                            </div>
                        </div>
                    </td>
                </tr>`;
        }).join('');

        // Auto-load matrix of the first/active guide
        const activeGuide = result.fare_guides.find(g => g.status === 'active') || result.fare_guides[0];
        if (activeGuide) {
            viewFareMatrix(activeGuide.id, false);
        } else {
            const section = document.getElementById('fareMatrixSection');
            if (section) section.style.display = 'none';
        }
    } catch (err) {
        console.error('Failed to load fare guides:', err);
    }
}

// ── Fare Matrix ───────────────────────────────────────────────────────────────

async function viewFareMatrix(guideId, shouldScroll = false) {
    try {
        const response = await fetch(muniApiUrl('get_fare_matrices', { guide_id: guideId }), { credentials: 'include' });
        const result   = await response.json();
        const section  = document.getElementById('fareMatrixSection');
        const tbody    = document.getElementById('fareMatrixBody');

        if (!result.fare_matrices || result.fare_matrices.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" style="text-align:center;padding:32px;color:var(--text-muted);">No fare data available</td></tr>`;
        } else {
            tbody.innerHTML = result.fare_matrices.map(m => `
                <tr>
                    <td>${m.distance_km} km</td>
                    <td>₱${parseFloat(m.regular_fare).toFixed(2)}</td>
                    <td>₱${parseFloat(m.discounted_fare).toFixed(2)}</td>
                </tr>`).join('');
        }

        section.style.display = 'block';
        if (shouldScroll) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
    } catch (err) {
        console.error('Failed to load fare matrix:', err);
    }
}

// ── Upload History ────────────────────────────────────────────────────────────

async function loadUploadHistory() {
    try {
        const response = await fetch(muniApiUrl('get_uploads'), { credentials: 'include' });
        const result   = await response.json();
        const tbody    = document.getElementById('uploadHistoryBody');

        if (!result.uploads || result.uploads.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted);">
                <i class="fas fa-inbox" style="font-size:24px;margin-bottom:8px;display:block;"></i>No upload history yet
            </td></tr>`;
            return;
        }

        tbody.innerHTML = result.uploads.map(upload => {
            const statusStyle = upload.status === 'completed' ? 'background:#d4edda;color:#155724;' :
                                upload.status === 'failed'    ? 'background:#f8d7da;color:#721c24;' :
                                                               'background:#fff3cd;color:#856404;';
            return `
                <tr>
                    <td>${escapeHtml(upload.file_name)}</td>
                    <td>${escapeHtml(upload.uploaded_by_name)}</td>
                    <td>${formatDate(upload.created_at)}</td>
                    <td><span style="padding:4px 8px;border-radius:4px;font-size:12px;${statusStyle}">${escapeHtml(upload.status)}</span></td>
                    <td>${upload.total_records} total / ${upload.valid_records} valid</td>
                    <td>
                        <button class="btn-gov btn-gov-secondary" style="padding:4px 8px;font-size:11px;" onclick="showImportLogs(${upload.id})">
                            <i class="fas fa-list"></i> Logs
                        </button>
                        <button class="btn-gov btn-gov-secondary" style="padding:4px 8px;font-size:11px;margin-left:4px;" onclick="showValidationErrors(${upload.id})">
                            <i class="fas fa-exclamation-triangle"></i> Errors
                        </button>
                    </td>
                </tr>`;
        }).join('');
    } catch (err) {
        console.error('Failed to load upload history:', err);
    }
}

// ── Sync / Activate ───────────────────────────────────────────────────────────

let _guideToSync = null;
let _syncStatus  = null;

function syncFareGuide(guideId, status) {
    _guideToSync = guideId;
    _syncStatus  = status;
    closeAllDropdowns();

    document.getElementById('modalTitle').textContent = 'Update Fare Guide';
    document.getElementById('modalBody').innerHTML = `
        <div style="text-align:center;padding:20px;">
            <i class="fas fa-info-circle" style="font-size:48px;color:#007bff;margin-bottom:16px;"></i>
            <p style="margin:0 0 24px;font-size:18px;">Are you sure you want to <strong>activate</strong> this fare guide?</p>
            <div style="display:flex;gap:12px;justify-content:center;">
                <button class="btn-gov btn-gov-secondary" onclick="cancelSync()"><i class="fas fa-times"></i> Cancel</button>
                <button class="btn-gov" onclick="confirmSync()"><i class="fas fa-check"></i> Confirm</button>
            </div>
        </div>`;
    document.getElementById('detailsModal').style.display = 'flex';
}

async function confirmSync() {
    if (!_guideToSync) return;
    try {
        const response = await fetch(muniApiUrl('sync_fare_guide'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ guide_id: _guideToSync, status: _syncStatus })
        });
        const result = await response.json();
        if (result.success) {
            document.getElementById('detailsModal').style.display = 'none';
            showSuccessMessage('Fare guide updated successfully!');
            loadFareGuides();
        } else {
            showErrorMessage('Failed to update: ' + (result.error || 'Unknown error'));
        }
    } catch (err) {
        showErrorMessage('Failed to update: ' + err.message);
    }
    _guideToSync = null; _syncStatus = null;
}

function cancelSync() {
    document.getElementById('detailsModal').style.display = 'none';
    _guideToSync = null; _syncStatus = null;
}

// ── Logs & Errors ─────────────────────────────────────────────────────────────

async function showImportLogs(uploadId) {
    try {
        const response = await fetch(muniApiUrl('get_import_logs', { upload_id: uploadId }), { credentials: 'include' });
        const result   = await response.json();
        let content = '';
        if (!result.import_logs || result.import_logs.length === 0) {
            content = '<p style="color:var(--text-muted);text-align:center;padding:20px;">No logs available</p>';
        } else {
            content = result.import_logs.map(log => {
                const icon  = log.severity === 'error' ? 'exclamation-circle' : log.severity === 'warning' ? 'exclamation-triangle' : 'info-circle';
                const color = log.severity === 'error' ? '#721c24' : log.severity === 'warning' ? '#856404' : '#004085';
                return `<div style="padding:8px;border-bottom:1px solid var(--border);color:${color};">
                    <i class="fas fa-${icon}"></i>
                    <strong>${escapeHtml(log.action)}</strong>
                    <span style="margin-left:8px;font-size:12px;color:var(--text-muted);">${formatDate(log.created_at)}</span>
                    <div style="margin-top:4px;font-size:14px;">${escapeHtml(log.details)}</div>
                </div>`;
            }).join('');
        }
        showModal('Import Logs', `<div style="padding:16px;">${content}</div>`);
    } catch (err) {
        console.error('Failed to load import logs:', err);
    }
}

async function showValidationErrors(uploadId) {
    try {
        const response = await fetch(muniApiUrl('get_validation_errors', { upload_id: uploadId }), { credentials: 'include' });
        const result   = await response.json();
        let content = '';
        if (!result.validation_errors || result.validation_errors.length === 0) {
            content = '<p style="color:var(--text-muted);text-align:center;padding:20px;">No validation errors found</p>';
        } else {
            content = `<table style="width:100%;border-collapse:collapse;">
                <thead><tr style="background:var(--border);">
                    <th style="padding:8px;text-align:left;">Row</th>
                    <th style="padding:8px;text-align:left;">Field</th>
                    <th style="padding:8px;text-align:left;">Error</th>
                    <th style="padding:8px;text-align:left;">Value</th>
                </tr></thead>
                <tbody>${result.validation_errors.map(err => `
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:8px;">${err.row_number || '-'}</td>
                        <td style="padding:8px;">${escapeHtml(err.field_name || '-')}</td>
                        <td style="padding:8px;">${escapeHtml(err.error_message)}</td>
                        <td style="padding:8px;font-family:monospace;">${escapeHtml(err.invalid_value || '-')}</td>
                    </tr>`).join('')}
                </tbody></table>`;
        }
        showModal('Validation Errors', content);
    } catch (err) {
        console.error('Failed to load validation errors:', err);
    }
}

// ── Dropdown ──────────────────────────────────────────────────────────────────

function toggleDropdown(event, guideId) {
    event.stopPropagation();
    closeAllDropdowns();
    const dropdown = document.getElementById(`dropdown-${guideId}`);
    if (dropdown) dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

function closeAllDropdowns() {
    document.querySelectorAll('.dropdown-menu').forEach(d => { d.style.display = 'none'; });
}

document.addEventListener('click', closeAllDropdowns);

// ── Modal Helpers ─────────────────────────────────────────────────────────────

function showModal(title, content) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalBody').innerHTML    = content;
    document.getElementById('detailsModal').style.display = 'flex';
}

function showSuccessMessage(message) {
    showModal('Success', `
        <div style="text-align:center;padding:30px;">
            <i class="fas fa-check-circle" style="font-size:64px;color:#28a745;margin-bottom:20px;"></i>
            <p style="margin:0;font-size:18px;color:#333;">${escapeHtml(message)}</p>
            <button class="btn-gov" style="margin-top:24px;" onclick="document.getElementById('detailsModal').style.display='none'">OK</button>
        </div>`);
}

function showErrorMessage(message) {
    showModal('Error', `
        <div style="text-align:center;padding:30px;">
            <i class="fas fa-times-circle" style="font-size:64px;color:#dc3545;margin-bottom:20px;"></i>
            <p style="margin:0;font-size:18px;color:#333;">${escapeHtml(message)}</p>
            <button class="btn-gov" style="margin-top:24px;" onclick="document.getElementById('detailsModal').style.display='none'">OK</button>
        </div>`);
}

// ── Utilities ─────────────────────────────────────────────────────────────────

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}
