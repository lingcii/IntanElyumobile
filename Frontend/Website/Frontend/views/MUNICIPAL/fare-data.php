<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'municipal' && !str_ends_with($_SESSION['user_role'], '_mto')) {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'Municipal Fare Data';

ob_start();
?>
    <div class="fare-management-container">
        <div class="upload-section card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cloud-upload-alt"></i> Upload Fare Guide PDF</h3>
            </div>
            <div class="card-body">
                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-file-pdf" style="font-size: 48px; color: var(--lupto-primary); margin-bottom: 16px;"></i>
                    <p style="margin: 0 0 8px; color: var(--text-secondary);">Drag and drop your PDF fare guide here, or click to select</p>
                    <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Supported format: PDF</p>
                    <input type="file" id="fareFileInput" accept=".pdf,application/pdf" style="display: none;">
                    <button class="btn-gov" style="margin-top: 16px;" onclick="document.getElementById('fareFileInput').click();">
                        <i class="fas fa-folder-open"></i> Browse Files
                    </button>
                </div>
                <div id="uploadProgress" style="display: none; margin-top: 16px;">
                    <div class="progress-bar" style="width: 100%; height: 20px; background: var(--border); border-radius: 10px; overflow: hidden;">
                        <div class="progress-fill" id="progressFill" style="width: 0%; height: 100%; background: var(--lupto-primary); transition: width 0.3s;"></div>
                    </div>
                    <p id="progressText" style="margin-top: 8px; color: var(--text-secondary);"></p>
                </div>
                <div id="uploadResult" style="display: none; margin-top: 16px;"></div>
            </div>
        </div>

        <div class="fare-guides-section card" style="margin-top: 16px; overflow: visible;">
            <div class="card-header flex-between">
                <h3 class="card-title"><i class="fas fa-book"></i> Fare Guides</h3>
                <button class="btn-gov btn-gov-secondary" onclick="loadFareGuides()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            <div class="card-body" style="padding: 0; overflow: visible;">
                <table class="data-table" id="fareGuidesTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Vehicle Type</th>
                            <th>Region</th>
                            <th>Effective Date</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th style="width: 80px;"></th>
                        </tr>
                    </thead>
                    <tbody id="fareGuidesBody">
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="fare-matrix-section card" style="margin-top: 16px; display: none;" id="fareMatrixSection">
            <div class="card-header flex-between">
                <h3 class="card-title"><i class="fas fa-table"></i> Fare Matrix</h3>
                <button class="btn-gov btn-gov-secondary" onclick="document.getElementById('fareMatrixSection').style.display = 'none';">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
            <div class="card-body" style="padding: 0; max-height: 500px; overflow-y: auto;">
                <table class="data-table" id="fareMatrixTable">
                    <thead style="position: sticky; top: 0; background: white; z-index: 10;">
                        <tr>
                            <th>Distance (km)</th>
                            <th>Regular Fare</th>
                            <th>Student/Elderly/Disabled</th>
                        </tr>
                    </thead>
                    <tbody id="fareMatrixBody">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="upload-history-section card" style="margin-top: 16px;">
            <div class="card-header flex-between">
                <h3 class="card-title"><i class="fas fa-history"></i> Upload History</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="data-table" id="uploadHistoryTable">
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
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="detailsModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center;">
            <div class="modal-content" style="max-width: 500px; width: 90%; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">
                <div class="modal-header flex-between" style="padding: 16px 20px; border-bottom: 1px solid #e0e0e0;">
                    <h3 id="modalTitle" style="margin: 0; font-size: 20px; font-weight: 600;">Details</h3>
                    <button class="btn-gov btn-gov-secondary" onclick="document.getElementById('detailsModal').style.display = 'none'" style="padding: 6px 12px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body" id="modalBody"></div>
            </div>
        </div>
    </div>

    <!-- Use MUNICIPAL-specific scripts -->
    <script src="../../scripts/functions/MUNICIPAL/fare-data-api.js"></script>
<?php
$pageContent = ob_get_clean();
if (is_ajax_request()) {
    if (isset($extraHeadContent)) {
        echo $extraHeadContent;
    }
    echo $pageContent;
    exit;
}
include '../../components/sections.php';
