<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'municipal' && !str_ends_with($_SESSION['user_role'], '_mto')) {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'Municipal Activity Logs';

ob_start();
?>
    <div class="flex-between" style="margin-bottom:16px;">
        <h2 class="section-title">Activity Logs</h2>
        <button class="btn-gov">
            <i class="fas fa-file-export"></i> Export Logs
        </button>
    </div>

    <!-- Filters -->
    <div class="lupto-analytics-filter-row">
        <select class="filter-select">
            <option>All Actions</option>
            <option>Tourist Spot Added</option>
            <option>Tourist Spot Updated</option>
            <option>Tourist Spot Approved</option>
            <option>Tourist Spot Rejected</option>
            <option>Fare Data Uploaded</option>
            <option>User Logged In</option>
        </select>
        <select class="filter-select">
            <option>All Modules</option>
            <option>Tourist Spots</option>
            <option>Approval Management</option>
            <option>Fare Data</option>
            <option>Users</option>
        </select>
        <div class="search-input-wrap">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Search logs...">
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>User</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#LOG-001</td>
                        <td><span class="badge badge-info">Tourist Spot Added</span></td>
                        <td>Tourist Spots</td>
                        <td>New tourist spot "Urbiztondo Surf Spot" added</td>
                        <td>San Juan MTO</td>
                        <td><?= date('M d, Y h:i:s A') ?></td>
                    </tr>
                    <tr>
                        <td>#LOG-002</td>
                        <td><span class="badge badge-success">Tourist Spot Approved</span></td>
                        <td>Approval Management</td>
                        <td>Tourist spot "Pebble Beach of Luna" approved</td>
                        <td>Municipal Admin</td>
                        <td><?= date('M d, Y h:i:s A', strtotime('-2 hours')) ?></td>
                    </tr>
                    <tr>
                        <td>#LOG-003</td>
                        <td><span class="badge badge-info">Fare Data Uploaded</span></td>
                        <td>Fare Data</td>
                        <td>New fare matrix uploaded (15 records)</td>
                        <td>Municipal Admin</td>
                        <td><?= date('M d, Y h:i:s A', strtotime('-1 day')) ?></td>
                    </tr>
                    <tr>
                        <td>#LOG-004</td>
                        <td><span class="badge badge-warning">User Logged In</span></td>
                        <td>Users</td>
                        <td>User "Municipal Admin" logged in</td>
                        <td>Municipal Admin</td>
                        <td><?= date('M d, Y h:i:s A', strtotime('-1 day')) ?></td>
                    </tr>
                    <tr>
                        <td>#LOG-005</td>
                        <td><span class="badge badge-danger">Tourist Spot Rejected</span></td>
                        <td>Approval Management</td>
                        <td>Tourist spot "Sample Spot" rejected (reason: incomplete info)</td>
                        <td>Municipal Admin</td>
                        <td><?= date('M d, Y h:i:s A', strtotime('-2 days')) ?></td>
                    </tr>
                </tbody>
            </table>
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
