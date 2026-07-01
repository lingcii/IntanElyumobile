<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'lupto') {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'LUPTO Analytics Dashboard';

ob_start();
?>
<link rel="stylesheet" href="../../css/LUPTO/analytics.css">
<?php
$extraHeadContent = ob_get_clean();
ob_start();
?>

<!-- ── Page Header ──────────────────────────────────────────── -->
<div class="pa-page-header">
    <h2><i class="fas fa-chart-line"></i> Tourism Analytics Dashboard</h2>
    <div class="pa-header-actions">
        <button class="btn-gov btn-gov-secondary" onclick="refreshAll()" title="Refresh all data">
            <i class="fas fa-sync-alt" id="refreshIcon"></i> Refresh
        </button>
    </div>
</div>

<!-- ── Global Filters ─────────────────────────────────────── -->
<div class="card" style="margin-bottom:16px;">
    <div class="pa-filter-bar">
        <select class="pa-filter-select" id="filterYear" onchange="refreshAll()" aria-label="Year">
            <option value="2026">2026</option>
            <option value="2025">2025</option>
        </select>
        <select class="pa-filter-select" id="filterMuni" onchange="refreshRankings()" aria-label="Municipality">
            <option value="">All Municipalities</option>
        </select>
        <select class="pa-filter-select" id="filterCategory" onchange="refreshRankings()" aria-label="Category">
            <option value="">All Categories</option>
            <option value="Beach">Beach</option>
            <option value="Mountain">Mountain</option>
            <option value="Historical">Historical</option>
            <option value="Waterfalls">Waterfalls</option>
            <option value="Adventure">Adventure</option>
            <option value="Farm">Farm</option>
            <option value="Religious">Religious</option>
            <option value="Other">Other</option>
        </select>
        <select class="pa-filter-select" id="filterStatus" onchange="refreshRankings()" aria-label="Classification">
            <option value="">All Statuses</option>
            <option value="EXIST">Existing</option>
            <option value="EMERGE">Emerging</option>
            <option value="POTENTIAL">Potential</option>
        </select>
        <button class="btn-gov btn-gov-secondary" onclick="clearFilters()">
            <i class="fas fa-times"></i> Clear
        </button>
    </div>
</div>

<!-- ── KPI Summary Cards ──────────────────────────────────── -->
<div class="pa-kpi-grid">
    <div class="pa-kpi-card">
        <div class="pa-kpi-icon blue"><i class="fas fa-city"></i></div>
        <div class="pa-kpi-info"><h4>Municipalities</h4><p id="kpiMunis">—</p></div>
    </div>
    <div class="pa-kpi-card">
        <div class="pa-kpi-icon green"><i class="fas fa-location-dot"></i></div>
        <div class="pa-kpi-info"><h4>Tourist Spots</h4><p id="kpiSpots">—</p></div>
    </div>
    <div class="pa-kpi-card">
        <div class="pa-kpi-icon orange"><i class="fas fa-users"></i></div>
        <div class="pa-kpi-info"><h4>Total Visits</h4><p id="kpiVisits">—</p></div>
    </div>
   
    <div class="pa-kpi-card">
        <div class="pa-kpi-icon gold"><i class="fas fa-trophy"></i></div>
        <div class="pa-kpi-info"><h4>Top Municipality</h4><p id="kpiTopMuni" style="font-size:14px; font-weight:700;">—</p></div>
    </div>
    
</div>

<!-- ════════════════════════════════════════════════════════
     TOP MUNICIPALITIES
═════════════════════════════════════════════════════════ -->
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
    <div class="card-body" style="padding-top:28px;">
        <div class="pa-podium-row" id="muniPodium">
            <div class="pa-loading"><i class="fas fa-spinner fa-spin"></i> Loading champions…</div>
        </div>
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

<!-- ════════════════════════════════════════════════════════
     TOP TOURIST SPOTS
═════════════════════════════════════════════════════════ -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <div class="pa-section-header">
            <h3 class="pa-section-title"><i class="fas fa-map-location-dot"></i> Top Tourist Spots</h3>
            <div class="pa-sort-tabs" id="spotSortTabs">
                <button class="pa-sort-tab active" data-sort="visits" onclick="setSpotSort(this,'visits')">Most Visited</button>
                <button class="pa-sort-tab"        data-sort="rating" onclick="setSpotSort(this,'rating')">Highest Rated</button>
                <button class="pa-sort-tab"        data-sort="newest" onclick="setSpotSort(this,'newest')">Newest</button>
            </div>
        </div>
    </div>
    <div class="card-body" style="padding-top:28px;">
        <div class="pa-podium-row" id="spotPodium">
            <div class="pa-loading"><i class="fas fa-spinner fa-spin"></i> Loading top spots…</div>
        </div>
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

<!-- ════════════════════════════════════════════════════════
     ANALYTICS CHARTS
═════════════════════════════════════════════════════════ -->

<!-- YoY Line chart — full width -->
<div class="pa-charts-grid" style="margin-bottom:16px;">
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

<!-- 2-column chart grid -->
<div class="pa-charts-grid">
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-bar"></i> Top Municipalities by Tourist Spots</h4>
        </div>
        <div class="pa-chart-body" style="height:260px;">
            <canvas id="spotsByMuniChart"></canvas>
        </div>
    </div>
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-bar"></i> Top Municipalities by Total Visits</h4>
        </div>
        <div class="pa-chart-body" style="height:260px;">
            <canvas id="visitsByMuniChart"></canvas>
        </div>
    </div>
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-pie"></i> Tourist Spots by Category</h4>
        </div>
        <div class="pa-chart-body" style="height:260px;">
            <canvas id="catDistChart"></canvas>
        </div>
    </div>
    <div class="pa-chart-card">
        <div class="pa-chart-header">
            <h4 class="pa-chart-title"><i class="fas fa-chart-pie"></i> Spots by Classification Status</h4>
        </div>
        <div class="pa-chart-body" style="height:260px;">
            <canvas id="classDistChart"></canvas>
        </div>
    </div>
</div>

<!-- Transport breakdown card -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bus"></i> Transportation Mode Distribution</h3>
    </div>
    <div class="card-body">
        <div style="height:220px;">
            <canvas id="transportChart"></canvas>
        </div>
        <div class="pa-transport-grid" id="transportBoxes">
            <div class="pa-transport-box"><span class="pa-transport-val" id="tCar">—</span><span class="pa-transport-label">Private Cars</span></div>
            <div class="pa-transport-box"><span class="pa-transport-val" id="tBus">—</span><span class="pa-transport-label">Tour Buses</span></div>
            <div class="pa-transport-box"><span class="pa-transport-val" id="tVan">—</span><span class="pa-transport-label">Vans</span></div>
            <div class="pa-transport-box"><span class="pa-transport-val" id="tOther">—</span><span class="pa-transport-label">Others</span></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="../../scripts/functions/LUPTO/analytics-api.js"></script>

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
