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
<?php
$extraHeadContent = ob_get_clean();

ob_start();
?>
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
                <h4>Approved Spots</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +1 today</span>
            </div>
            <div class="lupto-kpi-icon bg-green"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="pending-spots">
            <div class="lupto-kpi-info">
                <h4>Pending Spots</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-down"><i class="fas fa-arrow-down"></i> -3 this week</span>
            </div>
            <div class="lupto-kpi-icon bg-orange"><i class="fas fa-hourglass-half"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="total-visits">
            <div class="lupto-kpi-info">
                <h4>Total Monthly Visitors</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +10% this month</span>
            </div>
            <div class="lupto-kpi-icon bg-purple"><i class="fas fa-users"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="avg-rating">
            <div class="lupto-kpi-info">
                <h4>Average Rating</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-neutral"><i class="fas fa-minus"></i> Stable</span>
            </div>
            <div class="lupto-kpi-icon bg-yellow"><i class="fas fa-star"></i></div>
        </div>
    </div>

    <!-- Smart Insights Cards -->
    <div class="lupto-insights-grid">
        <div class="lupto-insight-card success">
            <div class="lupto-insight-icon"><i class="fas fa-trophy"></i></div>
            <div class="lupto-insight-content">
                <h4>Top Performer</h4>
                <p>Local Beach - 1,200 visitors this month</p>
            </div>
        </div>
        <div class="lupto-insight-card warning">
            <div class="lupto-insight-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="lupto-insight-content">
                <h4>Needs Attention</h4>
                <p>4 spots pending approval</p>
            </div>
        </div>
        <div class="lupto-insight-card info">
            <div class="lupto-insight-icon"><i class="fas fa-chart-line"></i></div>
            <div class="lupto-insight-content">
                <h4>Trend Alert</h4>
                <p>Visitor growth +10% from last month</p>
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
                <h3><i class="fas fa-chart-pie"></i> Spots by Category</h3>
            </div>
            <div class="lupto-chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

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
