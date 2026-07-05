<?php
require_once __DIR__ . '/../../session-bridge.php';
if ($_SESSION['user_role'] !== 'municipal' && !str_ends_with($_SESSION['user_role'], '_mto')) {
    header('Location: ../../login.php');
    exit;
}
$pageTitle = 'Municipal Fare Data';
ob_start();
?>
<link rel="stylesheet" href="../../css/MUNICIPAL/fare-data.css">

<div class="fd-container">

    <div class="fd-info-banner">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>Tricycle Fare Management</strong>
            <p>Municipal Tourism Offices can upload and manage <strong>Tricycle</strong> fare matrices only. All other vehicle types (PUB, PUJ, Van) are managed by PICTO.</p>
        </div>
    </div>

    <div class="fd-section-card">
        <div class="fd-section-header">
            <h3><i class="fas fa-cloud-upload-alt"></i> Upload Fare Guide PDF</h3>
        </div>
        <div class="fd-section-body-pad">
            <div class="fd-upload-zone" id="uploadArea">
                <i class="fas fa-file-pdf fd-upload-icon"></i>
                <p class="fd-upload-title">Drag and drop your PDF fare guide here, or click to browse</p>
                <p class="fd-upload-hint">Upload Tricycle fare matrix PDF (Max 20MB)</p>
                <input type="file" id="fareFileInput" accept=".pdf,application/pdf" style="display:none;">
                <button class="fd-btn-browse" onclick="document.getElementById('fareFileInput').click();">
                    <i class="fas fa-folder-open"></i> Browse Files
                </button>
            </div>
            <div class="fd-progress-wrap" id="uploadProgress">
                <div class="fd-progress-bar">
                    <div class="fd-progress-fill" id="progressFill"></div>
                </div>
                <p class="fd-progress-text" id="progressText"></p>
            </div>
            <div class="fd-upload-result" id="uploadResult"></div>
        </div>
    </div>

    <div class="fd-section-card">
        <div class="fd-section-header">
            <h3><i class="fas fa-book"></i> Fare Guides</h3>
            <button class="fd-btn-refresh" onclick="loadFareGuides()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
        <div class="fd-section-body">
            <table class="fd-data-table" id="fareGuidesTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Vehicle Type</th>
                        <th>Region</th>
                        <th>Effective Date</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody id="fareGuidesBody">
                    <tr class="fd-loading-row"><td colspan="7">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="fd-section-card fd-matrix-panel" id="fareMatrixSection">
        <div class="fd-section-header">
            <h3><i class="fas fa-table"></i> Fare Matrix</h3>
            <button class="fd-btn-refresh" onclick="document.getElementById('fareMatrixSection').classList.remove('active');">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
        <div class="fd-section-body fd-matrix-body-wrap">
            <table class="fd-data-table" id="fareMatrixTable">
                <thead>
                    <tr>
                        <th>Distance (km)</th>
                        <th>Regular Fare</th>
                        <th>Student / Senior / PWD</th>
                        <th>Savings</th>
                    </tr>
                </thead>
                <tbody id="fareMatrixBody"></tbody>
            </table>
        </div>
    </div>

    <div class="fd-section-card">
        <div class="fd-section-header">
            <h3><i class="fas fa-history"></i> Upload History</h3>
        </div>
        <div class="fd-section-body">
            <table class="fd-data-table" id="uploadHistoryTable">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Uploaded By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Records</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="uploadHistoryBody">
                    <tr class="fd-loading-row"><td colspan="6">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="fd-modal-overlay" id="detailsModal">
        <div class="fd-modal">
            <div class="fd-modal-header">
                <h3 id="modalTitle">Details</h3>
                <button class="fd-modal-close" onclick="document.getElementById('detailsModal').classList.remove('active');">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="fd-modal-body" id="modalBody"></div>
        </div>
    </div>

    <div class="fd-toast-container" id="fdToastContainer"></div>

</div>

<script src="../../scripts/functions/MUNICIPAL/fare-data-api.js"></script>
<?php
$pageContent = ob_get_clean();
if (is_ajax_request()) {
    if (isset($extraHeadContent)) echo $extraHeadContent;
    echo $pageContent;
    exit;
}
include '../../components/sections.php';
