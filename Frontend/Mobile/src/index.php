<?php
session_start();

// Extract view name safely - strip any extra query params appended to it
$rawView = isset($_GET['view']) ? $_GET['view'] : 'splash';
$view = preg_replace('/[^a-zA-Z0-9_]/', '', strtok($rawView, '&'));
$destinationId = isset($_GET['id']) ? (int) $_GET['id'] : null;
$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax']);

// Redirect hidden pages (no longer redirecting merch view for finals)

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
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="apple-touch-icon" href="assets/img/logo.png">
    <meta name="theme-color" content="#0a0a0e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.json">
    <?php 
    $baseHref = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/'; 
    ?>
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
    <link rel="stylesheet" href="assets/css/components/bottom_nav.css?v=<?= time() ?>">
    <!-- View Styles -->
    <link rel="stylesheet" href="assets/css/views/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/profile.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/settings.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/leaderboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/about.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/help.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/auth.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/edit_profile.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/terms.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/splash.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/map.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/itinerary.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/merch.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/trip_map.css?v=<?= time() ?>">
</head>
<body data-view="<?= htmlspecialchars($view) ?>">
    <!-- Global Drifting Clouds -->
    <div class="cloud-container" id="global-cloud-container">
        <!-- Shape A: Fluffy standard -->
        <div class="cloud cloud-1">
            <svg viewBox="0 0 100 50"><path fill="#ffffff" opacity="0.85" d="M 20 35 A 15 15 0 0 1 35 20 A 20 20 0 0 1 70 20 A 15 15 0 0 1 85 35 A 10 10 0 0 1 75 45 L 25 45 A 10 10 0 0 1 20 35 Z" /></svg>
        </div>
        <!-- Shape B: Wispy / long -->
        <div class="cloud cloud-2">
            <svg viewBox="0 0 100 50"><path fill="#ffffff" opacity="0.75" d="M 10 30 A 10 10 0 0 1 25 20 A 12 12 0 0 1 50 18 A 12 12 0 0 1 75 22 A 10 10 0 0 1 90 30 A 8 8 0 0 1 82 38 L 18 38 A 8 8 0 0 1 10 30 Z" /></svg>
        </div>
        <!-- Shape C: Tall / puffy -->
        <div class="cloud cloud-3">
            <svg viewBox="0 0 100 50"><path fill="#ffffff" opacity="0.65" d="M 20 30 A 12 12 0 0 1 30 15 A 16 16 0 0 1 55 10 A 16 16 0 0 1 80 18 A 12 12 0 0 1 90 30 L 10 30 Z" /></svg>
        </div>
        <!-- Shape A: Fluffy standard -->
        <div class="cloud cloud-4">
            <svg viewBox="0 0 100 50"><path fill="#ffffff" opacity="0.7" d="M 20 35 A 15 15 0 0 1 35 20 A 20 20 0 0 1 70 20 A 15 15 0 0 1 85 35 A 10 10 0 0 1 75 45 L 25 45 A 10 10 0 0 1 20 35 Z" /></svg>
        </div>
        <!-- Shape B: Wispy / long -->
        <div class="cloud cloud-5">
            <svg viewBox="0 0 100 50"><path fill="#ffffff" opacity="0.9" d="M 10 30 A 10 10 0 0 1 25 20 A 12 12 0 0 1 50 18 A 12 12 0 0 1 75 22 A 10 10 0 0 1 90 30 A 8 8 0 0 1 82 38 L 18 38 A 8 8 0 0 1 10 30 Z" /></svg>
        </div>
        <!-- Shape C: Tall / puffy -->
        <div class="cloud cloud-6">
            <svg viewBox="0 0 100 50"><path fill="#ffffff" opacity="0.55" d="M 20 30 A 12 12 0 0 1 30 15 A 16 16 0 0 1 55 10 A 16 16 0 0 1 80 18 A 12 12 0 0 1 90 30 L 10 30 Z" /></svg>
        </div>
        <!-- Shape A: Fluffy standard -->
        <div class="cloud cloud-7">
            <svg viewBox="0 0 100 50"><path fill="#ffffff" opacity="0.5" d="M 20 35 A 15 15 0 0 1 35 20 A 20 20 0 0 1 70 20 A 15 15 0 0 1 85 35 A 10 10 0 0 1 75 45 L 25 45 A 10 10 0 0 1 20 35 Z" /></svg>
        </div>
    </div>

    
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
        
        <?php
        $noNavViews = ['splash', 'auth', 'about', 'terms', 'edit_profile', 'help', 'trip_map', 'saved_trips', 'trending', 'reset-password', 'puzzles'];
        $navHiddenClass = in_array($view, $noNavViews) ? 'nav-hidden' : '';
        ?>
        <div id="bottom-navigation" class="<?= $navHiddenClass ?>">
            <?php include __DIR__ . '/components/bottom_nav.php'; ?>
        </div>
        <style>
            #bottom-navigation { transition: opacity 0.3s ease, transform 0.3s ease; }
            #bottom-navigation.nav-hidden { opacity: 0; pointer-events: none; transform: translateY(20px); }
        </style>
    </div>
    <script>
        window.backendUrl = 'https://intanelyu-production.up.railway.app';
        window.placeholderImage = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%231e293b%22 width=%22400%22 height=%22300%22/%3E%3Ctext x=%22200%22 y=%22150%22 text-anchor=%22middle%22 fill=%22%236b7280%22 font-size=%2220%22 font-family=%22sans-serif%22%3ENo Image%3C/text%3E%3C/svg%3E';
    </script>
    <script src="assets/js/main.js?v=<?= time() ?>"></script>
</body>
</html>
