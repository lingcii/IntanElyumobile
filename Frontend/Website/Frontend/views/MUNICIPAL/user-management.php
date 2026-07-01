<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'municipal' && !str_ends_with($_SESSION['user_role'], '_mto')) {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'Municipal User Management';

ob_start();
?>
    <div class="flex-between" style="margin-bottom: 16px;">
        <h2 class="section-title">User Account Management</h2>
        <button class="btn-gov">
            <i class="fas fa-user-plus"></i> Add New User
        </button>
    </div>

    <!-- User Stats -->
    <div class="lupto-user-stats-bar">
        <div class="lupto-user-stat-box">
            <span class="lupto-user-stat-label">Total Users</span>
            <span class="lupto-user-stat-value">24</span>
        </div>
        <div class="lupto-user-stat-box">
            <span class="lupto-user-stat-label">LUPTO Admin</span>
            <span class="lupto-user-stat-value">3</span>
        </div>
        <div class="lupto-user-stat-box">
            <span class="lupto-user-stat-label">Municipal TO</span>
            <span class="lupto-user-stat-value">20</span>
        </div>
        <div class="lupto-user-stat-box">
            <span class="lupto-user-stat-label">PITCO</span>
            <span class="lupto-user-stat-value">1</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="lupto-analytics-filter-row">
        <select class="filter-select">
            <option>All Roles</option>
            <option>LUPTO</option>
            <option>PITCO</option>
            <option>MTO</option>
        </select>
        <select class="filter-select">
            <option>All Statuses</option>
            <option>Active</option>
            <option>Inactive</option>
        </select>
        <div class="search-input-wrap">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Search user name/email...">
        </div>
    </div>

    <!-- User Cards Grid -->
    <div class="lupto-user-grid">
        <?php
        $users = [
            ['name' => 'Municipal Admin', 'email' => 'admin@municipal.gov.ph', 'role' => 'municipal', 'municipality' => 'Provincial Office', 'status' => 'Active'],
            ['name' => 'San Juan MTO', 'email' => 'mto.sanjuan@launion.gov.ph', 'role' => 'san_juan_mto', 'municipality' => 'San Juan', 'status' => 'Active'],
            ['name' => 'Luna MTO', 'email' => 'mto.luna@launion.gov.ph', 'role' => 'luna_mto', 'municipality' => 'Luna', 'status' => 'Active'],
            ['name' => 'PICTO Coordinator', 'email' => 'pitco@launion.gov.ph', 'role' => 'picto', 'municipality' => 'Provincial Office', 'status' => 'Active'],
            ['name' => 'Bauang MTO', 'email' => 'mto.bauang@launion.gov.ph', 'role' => 'bauang_mto', 'municipality' => 'Bauang', 'status' => 'Inactive'],
        ];
        
        foreach ($users as $user):
        ?>
            <div class="lupto-user-card">
                <div class="lupto-user-info-header">
                    <div>
                        <h4 class="lupto-user-name"><?= $user['name'] ?></h4>
                        <p class="lupto-user-email"><?= $user['email'] ?></p>
                    </div>
                    <span class="lupto-role-badge <?= $user['role'] ?>"><?= strtoupper($user['role']) ?></span>
                </div>
                <div class="lupto-user-meta-row">
                    <span><i class="fas fa-map-marker-alt"></i> <?= $user['municipality'] ?></span>
                    <span class="badge <?= $user['status'] == 'Active' ? 'badge-success' : 'badge-danger' ?>" style="padding:2px 6px; font-size:10px;"><?= $user['status'] ?></span>
                </div>
                <div class="lupto-user-actions">
                    <button class="btn-gov btn-gov-secondary" style="flex:1; padding:6px; font-size:11px;">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn-gov btn-gov-secondary" style="flex:1; padding:6px; font-size:11px;">
                        <i class="fas fa-key"></i> Reset
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
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
