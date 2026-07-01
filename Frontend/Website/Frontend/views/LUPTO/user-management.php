<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'lupto') {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'LUPTO User Management';

ob_start();
?>
    <div class="flex-between" style="margin-bottom: 16px;">
        <h2 class="section-title">User Account Management</h2>
        <button class="btn-gov">
            <i class="fas fa-sync-alt" id="refreshIcon"></i> Refresh
        </button>
    </div>

    <!-- Statistics Cards - will be populated by JS -->
    <div class="lupto-user-stats-bar">
        <div class="lupto-user-stat-box">
            <span class="lupto-user-stat-label">Total Users</span>
            <span class="lupto-user-stat-value" id="statTotal">—</span>
        </div>
        <div class="lupto-user-stat-box">
            <span class="lupto-user-stat-label">LUPTO Admin</span>
            <span class="lupto-user-stat-value" id="statLupto">—</span>
        </div>
        <div class="lupto-user-stat-box">
            <span class="lupto-user-stat-label">Municipal TO</span>
            <span class="lupto-user-stat-value" id="statMto">—</span>
        </div>
        <div class="lupto-user-stat-box">
            <span class="lupto-user-stat-label">PITCO</span>
            <span class="lupto-user-stat-value" id="statPitco">—</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="lupto-analytics-filter-row">
        <select class="filter-select" id="roleFilter" onchange="applyFilters()">
            <option value="">All Roles</option>
            <option value="lupto">LUPTO</option>
            <option value="picto">PITCO</option>
            <option value="municipal">MTO</option>
        </select>
        <select class="filter-select" id="statusFilter" onchange="applyFilters()">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <div class="search-input-wrap">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" placeholder="Search user name/email..." oninput="debouncedSearch()">
        </div>
    </div>

    <!-- Users Table -->
    <div class="lupto-user-grid" id="usersTableContainer">
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Municipality</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Last Login</th>
                </tr>
            </thead>
            <tbody id="usersBody">
                <tr>
                    <td colspan="8" style="text-align: center; padding: 32px; color: var(--text-muted);">
                        <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i> Loading users...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex-between" style="margin-top: 16px;">
        <span id="tableCountLabel" style="font-size: 13px; color: var(--text-muted);"></span>
        <div id="paginationBtns"></div>
    </div>

    <script src="../../scripts/api-config.js"></script>
    <script src="../../scripts/functions/LUPTO/user-management-api.js"></script>
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
