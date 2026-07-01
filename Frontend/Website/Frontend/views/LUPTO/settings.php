<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'lupto') {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'LUPTO Settings';

ob_start();
?>
    <h2 class="section-title">System Settings</h2>

    <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:16px;">
        <!-- General Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog"></i> General Settings</h3>
            </div>
            <div class="card-body">
                <div class="lupto-form-group">
                    <label>System Name</label>
                    <input type="text" class="filter-select" style="width:100%;" value="LUPTO - La Union Provincial Tourism Office">
                </div>
                <div class="lupto-form-group">
                    <label>Contact Email</label>
                    <input type="email" class="filter-select" style="width:100%;" value="lupto@launion.gov.ph">
                </div>
                <div class="lupto-form-group">
                    <label>Contact Number</label>
                    <input type="text" class="filter-select" style="width:100%;" value="+63 912 345 6789">
                </div>
                <div class="lupto-form-group">
                    <label>System Logo</label>
                    <input type="file" style="width:100%;">
                </div>
                <button class="btn-gov">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>

        <!-- Backup Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-database"></i> Backup Settings</h3>
            </div>
            <div class="card-body">
                <p style="margin-bottom:16px; color:var(--text-secondary);">Create or restore database backups.</p>
                <div style="display:flex; gap:8px; margin-bottom:16px;">
                    <button class="btn-gov">
                        <i class="fas fa-download"></i> Create Backup
                    </button>
                    <button class="btn-gov btn-gov-secondary">
                        <i class="fas fa-upload"></i> Restore Backup
                    </button>
                </div>
                <h4 style="margin-bottom:8px;">Recent Backups</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Date</th>
                            <th>Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>backup_20260619_120000.sql</td>
                            <td><?= date('M d, Y h:i A') ?></td>
                            <td>2.4 MB</td>
                        </tr>
                        <tr>
                            <td>backup_20260618_120000.sql</td>
                            <td><?= date('M d, Y', strtotime('-1 day')) ?></td>
                            <td>2.3 MB</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="card" style="grid-column:1/-1;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt"></i> Security Settings</h3>
            </div>
            <div class="card-body">
                <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:16px;">
                    <div class="lupto-form-group">
                        <label>Current Password</label>
                        <input type="password" class="filter-select" style="width:100%;">
                    </div>
                    <div class="lupto-form-group">
                        <label>New Password</label>
                        <input type="password" class="filter-select" style="width:100%;">
                    </div>
                    <div class="lupto-form-group">
                        <label>Confirm New Password</label>
                        <input type="password" class="filter-select" style="width:100%;">
                    </div>
                    <div class="lupto-form-group">
                        <label>Session Timeout (minutes)</label>
                        <input type="number" class="filter-select" style="width:100%;" value="30">
                    </div>
                </div>
                <button class="btn-gov">
                    <i class="fas fa-save"></i> Update Security Settings
                </button>
            </div>
        </div>
    </div>
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
