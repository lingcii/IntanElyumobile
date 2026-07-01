<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'picto') {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'PICTO Analytics Dashboard';

ob_start();
?>
<link rel="stylesheet" href="../../css/PICTO/analytics.css">
<?php
$extraHeadContent = ob_get_clean();
ob_start();
?>

 <!-- Page Header  -->
<div class="pa-page-header">
    <h2><i class="fas fa-chart-line"></i> Tourism Analytics Dashboard</h2>
    <div class="pa-header-actions">
        <button class="btn-gov btn-gov-secondary" onclick="refreshAll()" title="Refresh all data">
            <i class="fas fa-sync-alt" id="refreshIcon"></i> Refresh
        </button>
    </div>
</div>



 <!-- KPI Summary Cards  -->
<div class="pa-kpi-grid" id="kpiGrid">
    <?php
    $kpis = [
        ['id'=>'kpiMunis',    'icon'=>'blue',   'fa'=>'fa-city',       'label'=>'Municipalities'],
        ['id'=>'kpiVisits',   'icon'=>'orange', 'fa'=>'fa-users',       'label'=>'Total Visits'],
        ['id'=>'kpiTopMuni',  'icon'=>'gold',   'fa'=>'fa-trophy',      'label'=>'Top Municipality'],
        ['id'=>'kpiTopSpot',  'icon'=>'teal',   'fa'=>'fa-star',        'label'=>'Top Tourist Spot'],
    ];
    foreach ($kpis as $k): ?>
    <div class="pa-kpi-card">
        <div class="pa-kpi-icon <?= $k['icon'] ?>"><i class="fas <?= $k['fa'] ?>"></i></div>
        <div class="pa-kpi-info">
            <h4><?= $k['label'] ?></h4>
            <p id="<?= $k['id'] ?>">—</p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

     <!-- ANALYTICS CHARTS -->

<!-- Monthly trend line chart -->
<div class="pa-charts-grid">
    <div class="pa-chart-card wide">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-line"></i> Monthly Tourism Activity (Year-on-Year)</h4>
            <select class="pa-filter-select" id="trendYearSelect" onchange="loadTrendChart()" style="font-size:12px; padding:5px 10px;">
                <option value="2026">2026 vs 2025</option>
                <option value="2025">2025 vs 2024</option>
            </select>
        </div>
        <div class="pa-chart-body" style="height:280px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>

<div class="pa-charts-grid">
    <!-- Bar: spots by municipality -->
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-bar"></i> Top Municipalities by Tourist Spots</h4>
        </div>
        <div class="pa-chart-body" style="height:260px;">
            <canvas id="spotsByMuniChart"></canvas>
        </div>
    </div>

    <!-- Bar: visits by municipality -->
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-bar"></i> Top Municipalities by Total Visits</h4>
        </div>
        <div class="pa-chart-body" style="height:260px;">
            <canvas id="visitsByMuniChart"></canvas>
        </div>
    </div>

    <!-- Donut: category distribution -->
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-pie"></i> Tourist Spots by Category</h4>
        </div>
        <div class="pa-chart-body" style="height:260px;">
            <canvas id="catDistChart"></canvas>
        </div>
    </div>

    <!-- Donut: classification status -->
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-pie"></i> Spots by Classification Status</h4>
        </div>
        <div class="pa-chart-body" style="height:260px;">
            <canvas id="classDistChart"></canvas>
        </div>
    </div>
</div>


     <!-- TOP MUNICIPALITIES -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <div class="pa-section-header">
            <h3 class="pa-section-title"><i class="fas fa-trophy"></i> Top Municipalities</h3>
            <div class="pa-sort-tabs" id="muniSortTabs">
                <button class="pa-sort-tab active" data-sort="total_visits"   onclick="setMuniSort(this,'total_visits')">Most Visited</button>
                <button class="pa-sort-tab"        data-sort="total_spots"    onclick="setMuniSort(this,'total_spots')">Most Spots</button>
                <button class="pa-sort-tab"        data-sort="approved_spots" onclick="setMuniSort(this,'approved_spots')">Most Approved</button>
                <button class="pa-sort-tab"        data-sort="avg_rating"     onclick="setMuniSort(this,'avg_rating')">Top Rated</button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Ranks 1-10 -->
        <div class="pa-rank-table-wrap">
            <table class="data-table" id="muniTable" style="min-width:680px;">
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">Rank</th>
                        <th>Municipality</th>
                        <th>Total Spots</th>
                        <th>Approved</th>
                        <th>Total Visits</th>
                        <th>Avg Rating</th>
                    </tr>
                </thead>
                <tbody id="muniTableBody">
                    <tr><td colspan="6" class="pa-loading"><i class="fas fa-spinner fa-spin"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


     <!-- TOP TOURIST SPOTS -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <div class="pa-section-header">
            <h3 class="pa-section-title"><i class="fas fa-map-location-dot"></i> Top Tourist Spots</h3>
            <div class="pa-sort-tabs" id="spotSortTabs">
                <button class="pa-sort-tab active" data-sort="visits"  onclick="setSpotSort(this,'visits')">Most Visited</button>
                <button class="pa-sort-tab"        data-sort="rating"  onclick="setSpotSort(this,'rating')">Highest Rated</button>
                <button class="pa-sort-tab"        data-sort="newest"  onclick="setSpotSort(this,'newest')">Newest</button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Ranks 1-10 -->
        <div class="pa-rank-table-wrap">
            <table class="data-table" id="spotTable" style="min-width:720px;">
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">Rank</th>
                        <th>Tourist Spot</th>
                        <th>Municipality</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Visits</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody id="spotTableBody">
                    <tr><td colspan="7" class="pa-loading"><i class="fas fa-spinner fa-spin"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="../../scripts/functions/PITCO/analytics-api.js"></script>

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
