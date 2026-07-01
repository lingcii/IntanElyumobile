<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'municipal' && !str_ends_with($_SESSION['user_role'], '_mto')) {
    header('Location: ../../login.php');
    exit;
}
//title of the system
$pageTitle = 'Municipal Analytics';

ob_start();
?>
    <div class="flex-between" style="margin-bottom: 16px;">
        <h2 class="section-title">Analytics and Statistics</h2>
        <div class="lupto-analytics-filter-row" style="padding:0; border:none; margin:0;">
            <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
                <i class="fas fa-calendar"></i> Year:
                <select class="filter-select">
                    <option>2026</option>
                    <option>2025</option>
                </select>
            </label>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="lupto-analytics-grid">
        <!-- Monthly Trends Chart -->
        <div class="card lupto-chart-container" style="grid-column:1/-1;">
            <h3 class="card-title"><i class="fas fa-chart-area"></i> Month-on-Month Visitor Arrivals (YoY)</h3>
            <div style="height: 300px;">
                <canvas id="yoyChart"></canvas>
            </div>
        </div>

        <!-- Municipality Visits Bar Chart -->
        <div class="card lupto-chart-container" style="grid-column:1/-1;">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Monthly Visits by Municipality</h3>
            <div style="height: 250px;">
                <canvas id="muniBarChart"></canvas>
            </div>
        </div>

        <!-- Transport Distribution -->
        <div class="card">
            <h3 class="card-title"><i class="fas fa-bus"></i> Transportation Type Distribution</h3>
            <div style="height: 200px;">
                <canvas id="transportChart"></canvas>
            </div>
            <div class="lupto-transport-grid">
                <div class="lupto-transport-box">
                    <span class="lupto-transport-value">52%</span>
                    <span class="lupto-transport-label">Private Cars</span>
                </div>
                <div class="lupto-transport-box">
                    <span class="lupto-transport-value">13%</span>
                    <span class="lupto-transport-label">Tour Buses</span>
                </div>
                <div class="lupto-transport-box">
                    <span class="lupto-transport-value">27%</span>
                    <span class="lupto-transport-label">Vans</span>
                </div>
                <div class="lupto-transport-box">
                    <span class="lupto-transport-value">8%</span>
                    <span class="lupto-transport-label">Others</span>
                </div>
            </div>
        </div>

        <!-- Top Municipalities -->
        <div class="card">
            <h3 class="card-title"><i class="fas fa-trophy"></i> Top Performing Municipalities</h3>
            <table class="data-table" style="margin-top: 8px;">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Municipality</th>
                        <th>Total Visits</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>#1</strong></td>
                        <td>San Juan</td>
                        <td>128,000</td>
                    </tr>
                    <tr>
                        <td><strong>#2</strong></td>
                        <td>San Fernando City</td>
                        <td>98,000</td>
                    </tr>
                    <tr>
                        <td><strong>#3</strong></td>
                        <td>Bauang</td>
                        <td>72,000</td>
                    </tr>
                    <tr>
                        <td><strong>#4</strong></td>
                        <td>Agoo</td>
                        <td>58,000</td>
                    </tr>
                    <tr>
                        <td><strong>#5</strong></td>
                        <td>Luna</td>
                        <td>54,000</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
