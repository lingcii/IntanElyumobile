<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'municipal' && !str_ends_with($_SESSION['user_role'], '_mto')) {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'Municipal Analytics Dashboard';

ob_start();
?>
<link rel="stylesheet" href="../../css/LUPTO/analytics.css">
<link rel="stylesheet" href="../../css/MUNICIPAL/analytics.css">
<?php
$extraHeadContent = ob_get_clean();
ob_start();
?>

<!-- Page Header -->
<div class="pa-page-header">
    <h2><i class="fas fa-chart-line"></i> Municipal Tourism Analytics</h2>
    <div class="pa-header-actions">
        <label class="ma-auto-refresh">
            <input type="checkbox" id="autoRefreshToggle" onchange="toggleAutoRefresh()" checked>
            <span><i class="fas fa-sync-alt"></i> Auto-refresh 30s</span>
        </label>
        <div class="ma-export-group">
            <button class="btn-gov btn-gov-secondary" onclick="exportData('csv')" title="Export as CSV">
                <i class="fas fa-file-csv"></i> CSV
            </button>
            <button class="btn-gov btn-gov-secondary" onclick="exportData('pdf')" title="Export as PDF">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
        </div>
        <button class="btn-gov btn-gov-secondary" onclick="refreshAll()" title="Refresh all data">
            <i class="fas fa-sync-alt" id="refreshIcon"></i> Refresh
        </button>
    </div>
</div>

<!-- Year Filter -->
<div class="card" style="margin-bottom:16px;">
    <div class="pa-filter-bar">
        <label style="display:flex; align-items:center; gap:8px; font-weight:600; font-size:13px; color:var(--text-secondary);">
            <i class="fas fa-calendar"></i> Report Year:
            <select class="pa-filter-select" id="filterYear" onchange="refreshAll()">
                <option value="2026">2026</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
            </select>
        </label>
        <span style="flex:1;"></span>
        <small class="ma-muni-badge" id="muniBadge">
            <i class="fas fa-map-marker-alt"></i> <span id="muniName">Loading...</span>
        </small>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="pa-kpi-grid" id="kpiGrid">
    <div class="pa-kpi-card">
        <div class="pa-kpi-icon green"><i class="fas fa-location-dot"></i></div>
        <div class="pa-kpi-info"><h4>Total Spots</h4><p id="kpiTotalSpots">—</p></div>
    </div>
    <div class="pa-kpi-card">
        <div class="pa-kpi-icon blue"><i class="fas fa-check-circle"></i></div>
        <div class="pa-kpi-info"><h4>Approved Spots</h4><p id="kpiApproved">—</p></div>
    </div>
    <div class="pa-kpi-card">
        <div class="pa-kpi-icon orange"><i class="fas fa-users"></i></div>
        <div class="pa-kpi-info"><h4>Total Visits</h4><p id="kpiVisits">—</p></div>
    </div>
    <div class="pa-kpi-card">
        <div class="pa-kpi-icon teal"><i class="fas fa-chart-simple"></i></div>
        <div class="pa-kpi-info"><h4>Analytics Visits</h4><p id="kpiAnalytics">—</p></div>
    </div>
    <div class="pa-kpi-card">
        <div class="pa-kpi-icon purple"><i class="fas fa-star"></i></div>
        <div class="pa-kpi-info"><h4>Avg Rating</h4><p id="kpiAvgRating">—</p></div>
    </div>
    <div class="pa-kpi-card">
        <div class="pa-kpi-icon gold"><i class="fas fa-trophy"></i></div>
        <div class="pa-kpi-info"><h4>Top Category</h4><p id="kpiTopCat" style="font-size:14px; font-weight:700;">—</p></div>
    </div>
</div>

<!-- YoY Monthly Trend Line Chart -->
<div class="pa-charts-grid" style="margin-bottom:16px;">
    <div class="pa-chart-card wide">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-line"></i> Monthly Visitor Arrivals (Year-on-Year)</h4>
            <select class="pa-filter-select" id="trendYearSelect" onchange="loadTrendChart()" style="font-size:12px; padding:5px 10px;">
                <option value="2026">2026 vs 2025</option>
                <option value="2025">2025 vs 2024</option>
            </select>
        </div>
        <div class="pa-chart-body" style="height:300px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>

<!-- 2-column chart grid -->
<div class="pa-charts-grid">
    <!-- Category Distribution -->
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-pie"></i> Spots by Category</h4>
        </div>
        <div class="pa-chart-body" style="height:260px;">
            <canvas id="catDistChart"></canvas>
        </div>
    </div>

    <!-- Classification Status -->
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-tag"></i> Spots by Classification</h4>
        </div>
        <div class="pa-chart-body" style="height:260px;">
            <canvas id="classDistChart"></canvas>
        </div>
    </div>

    <!-- Transport Mode Distribution -->
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-bus"></i> Transportation Mode Distribution</h4>
        </div>
        <div class="pa-chart-body" style="height:220px;">
            <canvas id="transportChart"></canvas>
        </div>
        <div class="pa-transport-grid" id="transportBoxes">
            <div class="pa-transport-box"><span class="pa-transport-val" id="tCar">—</span><span class="pa-transport-label">Private Cars</span></div>
            <div class="pa-transport-box"><span class="pa-transport-val" id="tBus">—</span><span class="pa-transport-label">Tour Buses</span></div>
            <div class="pa-transport-box"><span class="pa-transport-val" id="tVan">—</span><span class="pa-transport-label">Vans</span></div>
            <div class="pa-transport-box"><span class="pa-transport-val" id="tOther">—</span><span class="pa-transport-label">Others</span></div>
        </div>
    </div>

    <!-- Top Spots by Visits -->
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-bar"></i> Top Spots by Visits</h4>
        </div>
        <div class="pa-chart-body" style="height:260px;">
            <canvas id="topSpotsChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Tourist Spots Table -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <div class="pa-section-header">
            <h3 class="pa-section-title"><i class="fas fa-map-location-dot"></i> Top Tourist Spots in Your Municipality</h3>
            <div class="pa-sort-tabs" id="spotSortTabs">
                <button class="pa-sort-tab active" data-sort="visits" onclick="setSpotSort(this,'visits')">Most Visited</button>
                <button class="pa-sort-tab"        data-sort="rating" onclick="setSpotSort(this,'rating')">Highest Rated</button>
                <button class="pa-sort-tab"        data-sort="newest" onclick="setSpotSort(this,'newest')">Newest</button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="pa-rank-table-wrap">
            <table class="data-table" id="spotTable" style="min-width:700px;">
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">Rank</th>
                        <th>Tourist Spot</th>
                        <th>Category</th>
                        <th>Classification</th>
                        <th>Visits</th>
                        <th>Rating</th>
                        <th>Entrance Fee</th>
                    </tr>
                </thead>
                <tbody id="spotTableBody">
                    <tr><td colspan="7" class="pa-loading"><i class="fas fa-spinner fa-spin"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:480px;">
        <div class="modal-header">
            <h3><i class="fas fa-file-export"></i> Export Analytics Data</h3>
            <button class="modal-close" onclick="closeExportModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding:20px;">
            <p style="margin-bottom:16px;">Select the data you want to export:</p>
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button class="btn-gov" onclick="triggerExport('csv','summary')"><i class="fas fa-file-csv"></i> Export Summary as CSV</button>
                <button class="btn-gov" onclick="triggerExport('csv','spots')"><i class="fas fa-file-csv"></i> Export Spots as CSV</button>
                <button class="btn-gov" onclick="triggerExport('csv','trends')"><i class="fas fa-file-csv"></i> Export Trends as CSV</button>
                <button class="btn-gov" onclick="triggerExport('csv','full')"><i class="fas fa-file-csv"></i> Export All Data as CSV</button>
                <button class="btn-gov btn-gov-secondary" onclick="triggerExport('pdf','full')"><i class="fas fa-file-pdf"></i> Export Full Report as PDF</button>
            </div>
        </div>
    </div>
    <div class="modal-backdrop" onclick="closeExportModal()"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="../../scripts/functions/MUNICIPAL/analytics-api.js"></script>

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
