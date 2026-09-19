<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

// Direct APK Binary Streaming Handler
if (
    (isset($_GET['action']) && $_GET['action'] === 'download_apk') ||
    (isset($_GET['download']) && $_GET['download'] === 'apk') ||
    (strpos($_SERVER['REQUEST_URI'] ?? '', 'intan-elyu.apk') !== false && !isset($_GET['view']))
) {
    $apkPath = dirname(__DIR__) . '/public/downloads/intan-elyu.apk';
    if (!file_exists($apkPath)) {
        $apkPath = __DIR__ . '/downloads/intan-elyu.apk';
    }
    if (file_exists($apkPath)) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.android.package-archive');
        header('Content-Disposition: attachment; filename="intan-elyu.apk"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($apkPath));
        readfile($apkPath);
        exit;
    } else {
        header('Location: https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/apks/intan-elyu.apk');
        exit;
    }
}

// Extract view name safely - from $_GET['view'] or URI path (e.g. /download)
// Detect if running inside the native Android APK vs regular web browser (Brave, Chrome, Safari, etc.)
$isApk = (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'IntanElyuAPK') !== false) || isset($_GET['app']);

// Default view: If accessed via web browser or search engine crawler with no view specified,
// default to 'download' (the official tourism portal website) so the site is rich, indexable, and searchable!
// If running inside the native Android APK, default to 'splash'.
$rawView = $isApk ? 'splash' : 'download';
if (isset($_GET['view'])) {
    $rawView = $_GET['view'];
} else {
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $pathSegments = array_values(array_filter(explode('/', $requestUri)));
    $lastSegment = end($pathSegments);
    if ($lastSegment && $lastSegment !== 'index.php' && !str_contains($lastSegment, '.')) {
        $rawView = $lastSegment;
    }
}
$view = preg_replace('/[^a-zA-Z0-9_\-]/', '', strtok($rawView, '&'));
if ($view === 'resetpassword') {
    $view = 'reset-password';
}
$destinationId = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Standalone Website Handling:
// If viewing the download page / official tourism website, serve it completely standalone
// so it is 100% decoupled from the mobile app container, mobile CSS rules, and mobile router.
if ($view === 'download') {
    $viewPath = __DIR__ . '/views/download.php';
    if (file_exists($viewPath)) {
        include $viewPath;
        exit;
    }
}

$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax']);

// If it's an AJAX request, just return the view content
if ($isAjax) {
    $viewPath = __DIR__ . '/views/' . $view . '.php';
    if (!file_exists($viewPath) && file_exists(__DIR__ . '/views/' . str_replace('-', '_', $view) . '.php')) {
        $viewPath = __DIR__ . '/views/' . str_replace('-', '_', $view) . '.php';
    }
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
    <!-- Early Console Error Shield for DevTools / Web-Vitals Extensions -->
    <script>
        (function () {
            function shouldSuppress(err) {
                if (!err) return false;
                var str = typeof err === 'string' ? err : (err.message || err.stack || String(err));
                return str.indexOf('startTime') !== -1 || str.indexOf('reportAllChanges') !== -1;
            }
            window.addEventListener('error', function (e) {
                if (shouldSuppress(e.message) || shouldSuppress(e.error)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return true;
                }
            }, true);
            window.addEventListener('unhandledrejection', function (e) {
                if (shouldSuppress(e.reason)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return true;
                }
            }, true);
            var prevOnError = window.onerror;
            window.onerror = function (msg, url, line, col, err) {
                if (shouldSuppress(msg) || shouldSuppress(err)) return true;
                if (typeof prevOnError === 'function') return prevOnError.apply(this, arguments);
                return false;
            };
            var origConsoleError = console.error;
            console.error = function () {
                var args = Array.prototype.slice.call(arguments);
                var fullStr = args.map(function (a) {
                    return typeof a === 'string' ? a : (a && (a.message || a.stack)) ? (a.message + ' ' + a.stack) : String(a);
                }).join(' ');
                if (shouldSuppress(fullStr)) return;
                return origConsoleError.apply(console, arguments);
            };
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover, interactive-widget=overlays-content">
    <title>Intan Elyu — Official Tourism Portal & Mobile App | Province of La Union</title>
    <meta name="description" content="Official smart tourism mobile platform and portal of the Provincial Government of La Union (PGLU). Discover 20 municipalities, attractions, surf spots, discounts, travel fares, and download the Intan Elyu mobile app.">
    <meta name="keywords" content="Intan Elyu, Intan Elyu mobile, Intan Elyu app, Intan Elyu download, Intan Elyu APK, La Union tourism, Elyu, San Juan surfing, PGLU, LUPTO, PICTO, Tangadan Falls, Balaoan Immuki Island, Luna Pebble Beach, Bauang grapes, La Union travel guide, mobile tourism app, Northern Luzon">
    <meta name="author" content="Provincial Government of La Union (PGLU)">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
    <link rel="canonical" href="https://app.intan-elyu.online/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Intan Elyu">
    <meta property="og:url" content="https://app.intan-elyu.online/">
    <meta property="og:title" content="Intan Elyu — Official Tourism Portal & Mobile App | Province of La Union">
    <meta property="og:description" content="Discover, explore, and experience the whole of La Union with Intan Elyu. Plan itineraries, discover 20 municipalities, view tourist spots, discounts, and earn gamified rewards.">
    <meta property="og:image" content="https://app.intan-elyu.online/assets/img/logo.png">
    <meta property="og:locale" content="en_PH">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Intan Elyu — Official Tourism Portal & Mobile App | Province of La Union">
    <meta name="twitter:description" content="Discover, explore, and experience the whole of La Union with Intan Elyu. Plan itineraries, discover 20 municipalities, view tourist spots, discounts, and earn gamified rewards.">
    <meta name="twitter:image" content="https://app.intan-elyu.online/assets/img/logo.png">
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="apple-touch-icon" href="assets/img/logo.png">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="mobile-web-app-capable" content="yes">
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


    <script>
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' || window.location.protocol === 'capacitor:' || window.location.protocol === 'file:') {
            if (window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform()) {
                window.backendUrl = 'https://api.intan-elyu.online';
            } else if (window.location.port === '3000') {
                window.backendUrl = 'http://localhost:8000';
            } else if (window.location.pathname.includes('/Intan-Elyu-Tourism-Management-System/')) {
                window.backendUrl = window.location.protocol + '//' + window.location.host + '/Intan-Elyu-Tourism-Management-System/backend/public';
            } else {
                window.backendUrl = 'https://api.intan-elyu.online';
            }
        } else if (window.location.hostname === 'app.intan-elyu.online') {
            window.backendUrl = 'https://api.intan-elyu.online';
        } else {
            window.backendUrl = window.BACKEND_URL || window.location.origin.replace('app.', 'api.');
        }
        window.GOOGLE_CLIENT_ID = '620598190857-37a0ucobfd4b3rct7ofti8rtvl3qt884.apps.googleusercontent.com';
    </script>
    <script src="assets/js/capacitor/capacitor.js?v=<?= time() ?>"></script>
    <script src="assets/js/main.js?v=<?= time() ?>"></script>

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
    <link rel="stylesheet" href="assets/css/views/discount.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/trip_map.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/saved_trips.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/trending.css?v=<?= time() ?>">
</head>

<body data-view="<?= htmlspecialchars($view) ?>">
    <!-- Global Drifting Clouds -->
    <div class="cloud-container" id="global-cloud-container">
        <!-- Shape A: Fluffy standard -->
        <div class="cloud cloud-1">
            <svg viewBox="0 0 100 50">
                <path fill="#ffffff" opacity="0.85"
                    d="M 20 35 A 15 15 0 0 1 35 20 A 20 20 0 0 1 70 20 A 15 15 0 0 1 85 35 A 10 10 0 0 1 75 45 L 25 45 A 10 10 0 0 1 20 35 Z" />
            </svg>
        </div>
        <!-- Shape B: Wispy / long -->
        <div class="cloud cloud-2">
            <svg viewBox="0 0 100 50">
                <path fill="#ffffff" opacity="0.75"
                    d="M 10 30 A 10 10 0 0 1 25 20 A 12 12 0 0 1 50 18 A 12 12 0 0 1 75 22 A 10 10 0 0 1 90 30 A 8 8 0 0 1 82 38 L 18 38 A 8 8 0 0 1 10 30 Z" />
            </svg>
        </div>
        <!-- Shape C: Tall / puffy -->
        <div class="cloud cloud-3">
            <svg viewBox="0 0 100 50">
                <path fill="#ffffff" opacity="0.65"
                    d="M 20 30 A 12 12 0 0 1 30 15 A 16 16 0 0 1 55 10 A 16 16 0 0 1 80 18 A 12 12 0 0 1 90 30 L 10 30 Z" />
            </svg>
        </div>
        <!-- Shape A: Fluffy standard -->
        <div class="cloud cloud-4">
            <svg viewBox="0 0 100 50">
                <path fill="#ffffff" opacity="0.7"
                    d="M 20 35 A 15 15 0 0 1 35 20 A 20 20 0 0 1 70 20 A 15 15 0 0 1 85 35 A 10 10 0 0 1 75 45 L 25 45 A 10 10 0 0 1 20 35 Z" />
            </svg>
        </div>
        <!-- Shape B: Wispy / long -->
        <div class="cloud cloud-5">
            <svg viewBox="0 0 100 50">
                <path fill="#ffffff" opacity="0.9"
                    d="M 10 30 A 10 10 0 0 1 25 20 A 12 12 0 0 1 50 18 A 12 12 0 0 1 75 22 A 10 10 0 0 1 90 30 A 8 8 0 0 1 82 38 L 18 38 A 8 8 0 0 1 10 30 Z" />
            </svg>
        </div>
        <!-- Shape C: Tall / puffy -->
        <div class="cloud cloud-6">
            <svg viewBox="0 0 100 50">
                <path fill="#ffffff" opacity="0.55"
                    d="M 20 30 A 12 12 0 0 1 30 15 A 16 16 0 0 1 55 10 A 16 16 0 0 1 80 18 A 12 12 0 0 1 90 30 L 10 30 Z" />
            </svg>
        </div>
        <!-- Shape A: Fluffy standard -->
        <div class="cloud cloud-7">
            <svg viewBox="0 0 100 50">
                <path fill="#ffffff" opacity="0.5"
                    d="M 20 35 A 15 15 0 0 1 35 20 A 20 20 0 0 1 70 20 A 15 15 0 0 1 85 35 A 10 10 0 0 1 75 45 L 25 45 A 10 10 0 0 1 20 35 Z" />
            </svg>
        </div>
    </div>


    <div id="app-container">
        <!-- Main Content Area -->
        <main id="main-content">
            <?php
            $viewPath = __DIR__ . '/views/' . $view . '.php';
            if (!file_exists($viewPath) && file_exists(__DIR__ . '/views/' . str_replace('-', '_', $view) . '.php')) {
                $viewPath = __DIR__ . '/views/' . str_replace('-', '_', $view) . '.php';
            }
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "<div class='container' style='margin-top: 50px; text-align:center;'><h2>View not found: " . htmlspecialchars($view) . "</h2></div>";
            }
            ?>
        </main>
    </div>

    <?php
    $noNavViews = ['splash', 'auth', 'about', 'terms', 'edit_profile', 'help', 'trip_map', 'saved_trips', 'saved_places', 'trending', 'reset-password', 'puzzles', 'discount', 'settings', 'user_manual', 'download'];
    $navHiddenClass = in_array($view, $noNavViews) ? 'nav-hidden' : '';
    ?>
    <!-- Bottom Navigation Bar (Locked to viewport bottom) -->
    <div id="bottom-navigation" class="<?= $navHiddenClass ?>">
        <?php include __DIR__ . '/components/bottom_nav.php'; ?>
    </div>
    <style>
        body[data-view="download"] #app-container,
        body[data-view="download"] #main-content {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        body[data-view="download"] #global-cloud-container {
            display: none !important;
        }
        #bottom-navigation {
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            height: 0 !important;
            overflow: visible !important;
            z-index: 1000 !important;
            pointer-events: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.32s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.4s ease !important;
        }

        #bottom-navigation.nav-hidden {
            opacity: 0 !important;
            pointer-events: none !important;
            transform: translateY(140px) !important;
            visibility: hidden !important;
        }

        #bottom-navigation.keyboard-hidden,
        body.keyboard-open #bottom-navigation,
        html.keyboard-open #bottom-navigation,
        body.map-search-active #bottom-navigation,
        body.sheet-open #bottom-navigation,
        html.sheet-open #bottom-navigation {
            opacity: 0 !important;
            pointer-events: none !important;
            transform: translateY(140px) !important;
            visibility: hidden !important;
        }
    </style>
</body>

</html>