<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'lupto') {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'LUPTO Fare Information';

ob_start();
?>
    <div class="fare-management-container">

        <!-- Read-only notice banner -->
        <div class="card" style="margin-bottom: 16px; background: #f0f7ff; border-left: 4px solid #0071c5;">
            <div class="card-body" style="padding: 14px 20px; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-info-circle" style="font-size: 20px; color: #0071c5;"></i>
                <div>
                    <strong style="color: #0071c5;">View-Only Access</strong>
                    <p style="margin: 2px 0 0; font-size: 13px; color: #555;">LUPTO can search, view, and download fare information. Upload and edit actions are managed by PITCO and Municipal Tourism Offices.</p>
                </div>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <div class="card" style="margin-bottom: 16px;">
            <div class="card-body" style="padding: 14px 20px;">
                <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                    <div style="flex: 1; min-width: 200px;">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search fare guides..." oninput="filterFareGuides()" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;">
                    </div>
                    <div>
                        <select id="vehicleFilter" onchange="filterFareGuides()" style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;">
                            <option value="">All Vehicle Types</option>
                            <option value="PUB_Aircon">PUB Aircon</option>
                            <option value="PUB_Ordinary">PUB Ordinary</option>
                            <option value="PUJ_Aircon">PUJ Aircon</option>
                            <option value="PUJ_Ordinary">PUJ Ordinary</option>
                            <option value="Tricycle">Tricycle</option>
                            <option value="Van">Van</option>
                        </select>
                    </div>
                    <button class="btn-gov btn-gov-secondary" onclick="loadFareGuides()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Fare Guides Table (view-only) -->
        <div class="fare-guides-section card" style="overflow: visible;">
            <div class="card-header flex-between">
                <h3 class="card-title"><i class="fas fa-book"></i> Fare Guides</h3>
                <span id="fareGuidesCount" style="font-size: 13px; color: var(--text-muted);"></span>
            </div>
            <div class="card-body" style="padding: 0; overflow: visible;">
                <table class="data-table" id="fareGuidesTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Vehicle Type</th>
                            <th>Region</th>
                            <th>Effective Date</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="fareGuidesBody">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Fare Matrix Viewer -->
        <div class="fare-matrix-section card" style="margin-top: 16px; display: none;" id="fareMatrixSection">
            <div class="card-header flex-between">
                <h3 class="card-title" id="fareMatrixTitle"><i class="fas fa-table"></i> Fare Matrix</h3>
                <div style="display: flex; gap: 8px;">
                    <button class="btn-gov btn-gov-secondary" onclick="exportFareMatrix()" title="Download as CSV">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                    <button class="btn-gov btn-gov-secondary" onclick="document.getElementById('fareMatrixSection').style.display = 'none';">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
            <div class="card-body" style="padding: 0; max-height: 500px; overflow-y: auto;">
                <table class="data-table" id="fareMatrixTable">
                    <thead style="position: sticky; top: 0; background: white; z-index: 10;">
                        <tr>
                            <th>Distance (km)</th>
                            <th>Regular Fare</th>
                            <th>Student / Elderly / Disabled</th>
                        </tr>
                    </thead>
                    <tbody id="fareMatrixBody"></tbody>
                </table>
            </div>
        </div>

        <!-- Shared Modal (view logs only) -->
        <div id="detailsModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center;">
            <div class="modal-content" style="max-width: 600px; width: 90%; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">
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

    <!-- LUPTO-specific fare script (view-only: no upload, no delete, no activate) -->
    <script src="../../scripts/api-config.js"></script>
    <script src="../../scripts/functions/LUPTO/fare-data-api.js"></script>
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
