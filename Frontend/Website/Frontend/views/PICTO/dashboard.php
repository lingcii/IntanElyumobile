<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'picto') {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'PICTO Dashboard';

// Extra head content for CSS and scripts that need to load in <head>
ob_start();
?>
    <!-- PITCO Dashboard CSS -->
    <link rel="stylesheet" href="../../css/LUPTO/dashboard.css">
    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<?php
$extraHeadContent = ob_get_clean();

ob_start();
?>


    <!-- Summary Cards -->
    <div class="lupto-kpi-grid">
        <div class="lupto-kpi-card" data-kpi="total-municipalities">
            <div class="lupto-kpi-info">
                <h4>Total Municipalities</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-neutral"><i class="fas fa-minus"></i> No Change</span>
            </div>
            <div class="lupto-kpi-icon bg-blue"><i class="fas fa-city"></i></div>
        </div>
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

    <!-- Smart Insights Cards
    <div class="lupto-insights-grid">
        <div class="lupto-insight-card success">
            <div class="lupto-insight-icon"><i class="fas fa-trophy"></i></div>
            <div class="lupto-insight-content">
                <h4>Top Performer</h4>
                <p>Bauang Beach - 5,200 visitors this month</p>
            </div>
        </div>
        <div class="lupto-insight-card warning">
            <div class="lupto-insight-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="lupto-insight-content">
                <h4>Needs Attention</h4>
                <p>15 spots pending approval</p>
            </div>
        </div>
        <div class="lupto-insight-card info">
            <div class="lupto-insight-icon"><i class="fas fa-chart-line"></i></div>
            <div class="lupto-insight-content">
                <h4>Trend Alert</h4>
                <p>Visitor growth +12% from last month</p>
            </div>
        </div>
        <div class="lupto-insight-card danger">
            <div class="lupto-insight-icon"><i class="fas fa-star-half-alt"></i></div>
            <div class="lupto-insight-content">
                <h4>Quality Alert</h4>
                <p>3 attractions below 4-star rating</p>
            </div>
        </div>
    </div> -->

    <!-- Map & Activities -->
    <div class="lupto-dashboard-main-grid">
        <!-- Map Preview & Details Panel -->
        <div>
            <div class="card" style="padding: 14px;">
                <div class="lupto-map-header-action">
                    <h3 class="card-title" style="font-size: 14px; margin: 0;">
                        <i class="fas fa-map"></i> La Union Interactive LGU Profile Map
                    </h3>
                </div>
                <div id="dashboard-map" class="lupto-embedded-map"></div>
            </div>


        </div>

        <!-- Recent Activities -->
        <div class="lupto-recent-activities">
            <h3><i class="fas fa-history"></i> Recent Activities</h3>
            <div class="lupto-activity-item added">
                <div class="lupto-activity-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="lupto-activity-content">
                    <h4>Tourist Spot Added</h4>
                    <p><strong>Urbiztondo Surf Spot</strong> added by San Juan MTO</p>
                    <span class="lupto-activity-time"><i class="far fa-clock"></i> <?= date('M d, Y h:i A', strtotime('-5 minutes')) ?></span>
                </div>
            </div>
            <div class="lupto-activity-item approved">
                <div class="lupto-activity-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="lupto-activity-content">
                    <h4>Tourist Spot Approved</h4>
                    <p><strong>Pebble Beach of Luna</strong> approved by LUPTO Admin</p>
                    <span class="lupto-activity-time"><i class="far fa-clock"></i> <?= date('M d, Y h:i A', strtotime('-2 hours')) ?></span>
                </div>
            </div>
            <div class="lupto-activity-item uploaded">
                <div class="lupto-activity-icon">
                    <i class="fas fa-file-upload"></i>
                </div>
                <div class="lupto-activity-content">
                    <h4>Transport Fare Uploaded</h4>
                    <p>Fare matrix for San Juan to San Fernando uploaded</p>
                    <span class="lupto-activity-time"><i class="far fa-clock"></i> <?= date('M d, Y h:i A', strtotime('-1 day')) ?></span>
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

        <!-- Spots by Category — horizontal bar chart, one bar per category -->
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
                <h3><i class="fas fa-chart-bar"></i> Top 5 Municipalities</h3>
            </div>
            <div class="lupto-chart-container">
                <canvas id="topMunicipalitiesChart"></canvas>
            </div>
        </div>
        
    </div>

    <!-- Leaflet Map -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Dashboard Scripts -->
    <script src="../../scripts/functions/PITCO/dashboard-api.js"></script>
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
