<?php
session_start();

// Extract view name safely - strip any extra query params appended to it
$rawView = isset($_GET['view']) ? $_GET['view'] : 'splash';
$view = preg_replace('/[^a-zA-Z0-9_]/', '', strtok($rawView, '&'));
$destinationId = isset($_GET['id']) ? (int) $_GET['id'] : null;
$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax']);

// If it's an AJAX request, just return the view content
if ($isAjax) {
    $viewPath = __DIR__ . '/views/' . $view . '.php';
    if (file_exists($viewPath)) {
        include $viewPath;
    } else {
        echo "<div class='container' style='margin-top: 50px; text-align:center;'><h2>View not found: " . htmlspecialchars($view) . "</h2></div>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Intan Elyu</title>
    <?php $baseHref = (strpos($_SERVER['REQUEST_URI'], '/mobile') !== false) ? '/mobile/' : '/'; ?>
    <base href="<?= $baseHref ?>">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet Map & Clustering -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

    <!-- MapLibre GL JS (for 3D Map View) -->
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" />
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>

    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <!-- Component Styles -->
    <link rel="stylesheet" href="assets/css/components/header.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/components/sidebar.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/components/bottom_nav.css?v=<?= time() ?>">
    <!-- View Styles -->
    <link rel="stylesheet" href="assets/css/views/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/profile.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/settings.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/leaderboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/about.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/help.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/auth.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/setup_profile.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/terms.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/splash.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/map.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/itinerary.css?v=<?= time() ?>">
</head>
<body>

    
    <div id="app-container">
        <!-- Main Content Area -->
        <main id="main-content">
            <?php 
                $viewPath = __DIR__ . '/views/' . $view . '.php';
                if (file_exists($viewPath)) {
                    include $viewPath;
                } else {
                    echo "<div class='container' style='margin-top: 50px; text-align:center;'><h2>View not found: " . htmlspecialchars($view) . "</h2></div>";
                }
            ?>
        </main>
        
        <?php include __DIR__ . '/components/sidebar.php'; ?>
        <?php include __DIR__ . '/components/bottom_nav.php'; ?>
    </div>
    <script src="assets/js/main.js?v=<?= time() ?>"></script>
</body>
</html>
