<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'municipal' && !str_ends_with($_SESSION['user_role'], '_mto')) {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'MUNICIPAL Dashboard';

// Extra head content for CSS and scripts that need to load in <head>
ob_start();
?>
    <!-- MUNICIPAL Dashboard CSS -->
    <link rel="stylesheet" href="../../css/MUNICIPAL/dashboard.css">
    <link rel="stylesheet" href="../../css/LUPTO/dashboard.css">
    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<?php
$extraHeadContent = ob_get_clean();

ob_start();
?>
    <!-- Loading Overlay -->
    <div id="dashboard-loading-overlay" class="lupto-loading-overlay">
        <div class="lupto-loading-content">
            <i class="fas fa-chart-line" style="font-size:40px;color:#2563EB;margin-bottom:16px;"></i>
            <div class="lupto-loading-spinner"></div>
            <h3 style="margin:16px 0 6px 0;font-size:16px;font-weight:700;color:#1E293B;">Loading dashboard data</h3>
            <p style="margin:0;font-size:13px;color:#64748B;">Fetching KPIs, charts, and analytics...</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="lupto-kpi-grid">
        <div class="lupto-kpi-card" data-kpi="total-spots">
            <div class="lupto-kpi-info">
                <h4>Total Tourist Spots</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +2 this week</span>
            </div>
            <div class="lupto-kpi-icon bg-green"><i class="fas fa-compass"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="approved-spots">
            <div class="lupto-kpi-info">
                <h4>Total Open Tourist Spots</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +1 today</span>
            </div>
            <div class="lupto-kpi-icon bg-green"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="pending-spots">
            <div class="lupto-kpi-info">
                <h4>Total Closed Tourist Spots</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-down"><i class="fas fa-arrow-down"></i> -3 this week</span>
            </div>
            <div class="lupto-kpi-icon bg-orange"><i class="fas fa-hourglass-half"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="total-visits">
            <div class="lupto-kpi-info">
                <h4>Total Monthly Visitors</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +12% this month</span>
            </div>
            <div class="lupto-kpi-icon bg-purple"><i class="fas fa-users"></i></div>
        </div>
    
    </div>

    <!-- Map & Recent Activities -->
    <div class="lupto-dashboard-main-grid">
        <div>
            <div class="card" style="padding: 14px;">
                <div class="lupto-map-header-action">
                    <h3 class="card-title" style="font-size: 14px; margin: 0;">
                        <i class="fas fa-map"></i> Municipality Profile Map
                    </h3>
                </div>
                <div id="dashboard-map" class="lupto-embedded-map"></div>
            </div>
        </div>

        <!-- Smart Insights Cards — populated dynamically -->
        <div class="lupto-recent-activities">
            <h3><i class="fas fa-chart-pie"></i> Quick Insights</h3>
            <div class="lupto-insight-card success" style="margin-bottom:12px;">
                <div class="lupto-insight-icon"><i class="fas fa-trophy"></i></div>
                <div class="lupto-insight-content">
                    <h4>Top Performer</h4>
                    <p id="insight-top-spot">Loading...</p>
                </div>
            </div>
            <div class="lupto-insight-card warning" style="margin-bottom:12px;">
                <div class="lupto-insight-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="lupto-insight-content">
                    <h4>Needs Attention</h4>
                    <p id="insight-needs-attention">Loading...</p>
                </div>
            </div>
            <div class="lupto-insight-card info" style="margin-bottom:12px;">
                <div class="lupto-insight-icon"><i class="fas fa-chart-line"></i></div>
                <div class="lupto-insight-content">
                    <h4>Activity Overview</h4>
                    <p id="insight-trend">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="lupto-charts-grid">
        <div class="lupto-chart-card">
            <div class="lupto-chart-header">
                <h3><i class="fas fa-chart-line"></i> Visitor Trends (Last 12 Months)</h3>
            </div>
            <div class="lupto-chart-container">
                <canvas id="visitorTrendsChart"></canvas>
            </div>
        </div>

        <div class="lupto-chart-card">
            <div class="lupto-chart-header">
                <h3><i class="fas fa-chart-bar"></i> Spots by Category</h3>
            </div>
            <div class="lupto-chart-container" style="overflow-y:auto; max-height:420px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <div class="lupto-chart-card">
            <div class="lupto-chart-header">
                <h3><i class="fas fa-chart-bar"></i> Top Spots by Visits</h3>
            </div>
            <div class="lupto-chart-container">
                <canvas id="topSpotsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Leaflet Map -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Dashboard Scripts -->
    <script src="../../scripts/functions/MUNICIPAL/dashboard-api.js"></script>
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
