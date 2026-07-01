<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'lupto') {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'LUPTO – Tourist Leaderboard';

ob_start();
?>
<link rel="stylesheet" href="../../css/LUPTO/leaderboard.css">
<?php
$extraHeadContent = ob_get_clean();

ob_start();
?>

 <!-- Page Header  -->
<div class="lb-page-header">
    <h2><i class="fas fa-trophy"></i> Tourist Leaderboard</h2>
    <div class="lb-actions">
        <button class="btn-gov btn-gov-secondary" onclick="refreshAll()" title="Refresh data">
            <i class="fas fa-sync-alt" id="refreshIcon"></i> Refresh
        </button>
    </div>
</div>

 <!-- KPI Strip  -->

<div class="lb-kpi-strip" id="kpiStrip">
    <div class="lb-kpi-card">
        <div class="lb-kpi-icon blue"><i class="fas fa-users"></i></div>
        <div class="lb-kpi-info"><h4>Total Tourists</h4><p id="kpiUsers">—</p></div>
    </div>
    <div class="lb-kpi-card">
        <div class="lb-kpi-icon gold"><i class="fas fa-star"></i></div>
        <div class="lb-kpi-info"><h4>Highest Points</h4><p id="kpiHighest">—</p></div>
    </div>
    <div class="lb-kpi-card">
        <div class="lb-kpi-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="lb-kpi-info"><h4>Total Activities</h4><p id="kpiActivities">—</p></div>
    </div>
    <div class="lb-kpi-card">
        <div class="lb-kpi-icon purple"><i class="fas fa-gem"></i></div>
        <div class="lb-kpi-info"><h4>Points Earned</h4><p id="kpiGrandPoints">—</p></div>
    </div>
</div>

<!-- Top 3 Champions -->

<div class="lb-podium-section" id="podiumSection">
    <div class="lb-podium-title">🏆 &nbsp; Top 3 Champions &nbsp; 🏆</div>
    <div class="lb-podium-wrapper" id="podiumWrapper">
        <div class="lb-empty" style="color:#94a3b8;">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading champions...</p>
        </div>
    </div>
</div>

 <!-- Search / Filter Bar  -->
<div class="card" style="margin-bottom:12px;">
    <div class="lb-controls-bar">
        <div class="lb-search-wrap">
            <i class="fas fa-search"></i>
            <input
                type="text"
                id="searchInput"
                class="lb-search-input"
                placeholder="Search by name or User ID…"
                oninput="debouncedSearch()"
                aria-label="Search users">
        </div>
        <select class="lb-filter-select" id="sortSelect" onchange="applyFilters()" aria-label="Sort by">
            <option value="points_desc">Highest Points</option>
            <option value="points_asc">Lowest Points</option>
            <option value="activities_desc">Most Activities</option>
            <option value="name_asc">Name A→Z</option>
        </select>
        <button class="btn-gov btn-gov-secondary" onclick="clearSearch()" title="Clear search">
            <i class="fas fa-times"></i> Clear
        </button>
    </div>
</div>

<!-- Top 100 Table  -->
<div class="card lb-table-card" style="position:relative;">
    <div class="card-header flex-between">
        <h3 class="card-title"><i class="fas fa-list-ol"></i> Top 100 Leaderboard</h3>
        <span id="tableCountLabel" style="font-size:12px; color:var(--text-muted);"></span>
    </div>
    <div style="overflow-x:auto; position:relative;">
        <table class="data-table" id="leaderboardTable" style="min-width:700px;">
            <thead>
                <tr>
                    <th style="width:56px; text-align:center;">Rank</th>
                    <th style="width:72px; text-align:center;">User ID</th>
                    <th>Full Name</th>
                    <th>Total Points</th>
                    <th style="text-align:center;">Completed Activities</th>
                    <th>Last Activity</th>
                </tr>
            </thead>
            <tbody id="leaderboardBody">
                <tr>
                    <td colspan="6" class="lb-empty">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading leaderboard…</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div class="lb-pagination" id="paginationBar">
        <span id="paginationInfo"></span>
        <div class="lb-page-btns" id="paginationBtns"></div>
    </div>
</div>

<script src="../../scripts/functions/LUPTO/leaderboard-api.js"></script>

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
