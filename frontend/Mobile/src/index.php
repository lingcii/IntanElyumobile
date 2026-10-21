<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

$reqUri = $_SERVER['REQUEST_URI'] ?? '/';
$reqPath = parse_url($reqUri, PHP_URL_PATH);

// Health check endpoint for Railway/cloud monitoring
if ($reqPath === '/up' || $reqPath === '/health') {
    http_response_code(200);
    header('Content-Type: text/plain');
    echo 'OK';
    exit;
}

// Canonical Host Redirection:
// If accessed via default Railway subdomain (*.railway.app),
// permanently redirect (301) to official custom domain (https://app.intan-elyu.online)
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
if (strpos($httpHost, 'railway.app') !== false) {
    header('Location: https://app.intan-elyu.online' . $reqUri, true, 301);
    exit;
}

// Direct APK Binary Streaming Handler
if (
    (isset($_GET['action']) && $_GET['action'] === 'download_apk') ||
    (isset($_GET['download']) && $_GET['download'] === 'apk') ||
    (strpos($_SERVER['REQUEST_URI'] ?? '', 'intan-elyu.apk') !== false && !isset($_GET['view']))
) {
    $candidatePaths = [
        dirname(__DIR__) . '/android/app/build/outputs/apk/release/app-release.apk',
        dirname(__DIR__) . '/android/app/build/outputs/apk/debug/app-debug.apk',
        dirname(__DIR__) . '/public/downloads/intan-elyu.apk',
        dirname(__DIR__) . '/app-release.apk',
        dirname(__DIR__) . '/app-debug.apk',
        __DIR__ . '/downloads/intan-elyu.apk',
    ];
    $apkPath = null;
    foreach ($candidatePaths as $candidate) {
        if (file_exists($candidate) && filesize($candidate) > 10 * 1024 * 1024) {
            $apkPath = $candidate;
            break;
        }
    }
    if (!$apkPath) {
        foreach ($candidatePaths as $candidate) {
            if (file_exists($candidate) && filesize($candidate) > 0) {
                $apkPath = $candidate;
                break;
            }
        }
    }

    if ($apkPath && file_exists($apkPath)) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        $fileSize = filesize($apkPath);
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.android.package-archive');
        header('Content-Disposition: attachment; filename="intan-elyu.apk"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Content-Length: ' . $fileSize);
        header('Accept-Ranges: bytes');

        $handle = fopen($apkPath, 'rb');
        if ($handle) {
            while (!feof($handle) && connection_status() === 0) {
                echo fread($handle, 1024 * 1024);
                flush();
            }
            fclose($handle);
        } else {
            readfile($apkPath);
        }
        exit;
    } else {
        // Fallback: If no local file on disk (e.g. Railway cloud container), stream directly from Cloudflare R2
        // to bypass mobile ISP DNS resolution failures (ERR_NAME_NOT_RESOLVED / -105) on the *.r2.dev domain!
        $remoteApkUrl = getenv('CLOUDFLARE_R2_APK_URL') ?: (rtrim(getenv('CLOUDFLARE_R2_URL') ?: 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev', '/') . '/apks/intan-elyu.apk?v=0.0.0');
        $preferRedirect = isset($_GET['redirect']) && $_GET['redirect'] === '1';
        $isCustomDomain = (strpos($remoteApkUrl, 'r2.dev') === false);

        // If using a verified custom domain (e.g. download.intan-elyu.online) or explicitly requested redirect
        if ($preferRedirect || $isCustomDomain) {
            header('Location: ' . $remoteApkUrl);
            exit;
        }

        // Stream via server so mobile clients never query pub-*.r2.dev DNS directly
        @set_time_limit(0);
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 30,
                'follow_location' => 1,
                'header' => "User-Agent: IntanElyuStreamer/1.0\r\n"
            ]
        ]);

        $remoteHandle = @fopen($remoteApkUrl, 'rb', false, $ctx);
        if ($remoteHandle) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            $meta = stream_get_meta_data($remoteHandle);
            $headers = $meta['wrapper_data'] ?? [];
            $contentLength = null;
            foreach ($headers as $hdr) {
                if (stripos($hdr, 'Content-Length:') === 0) {
                    $contentLength = trim(substr($hdr, 15));
                    break;
                }
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.android.package-archive');
            header('Content-Disposition: attachment; filename="intan-elyu.apk"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            if ($contentLength) {
                header('Content-Length: ' . $contentLength);
            }
            header('Accept-Ranges: bytes');

            while (!feof($remoteHandle) && connection_status() === 0) {
                echo fread($remoteHandle, 512 * 1024);
                flush();
            }
            fclose($remoteHandle);
            exit;
        }

        // Final fallback redirect if stream opening failed
        header('Location: ' . $remoteApkUrl);
        exit;
    }
}

// Extract view name safely - from $_GET['view'] or URI path (e.g. /download)
// Detect if running inside the native Android APK vs regular web browser (Brave, Chrome, Safari, etc.)
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$serverHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocalDev = (strpos($serverHost, 'localhost') !== false) ||
    (strpos($serverHost, '127.0.0.1') !== false) ||
    (strpos($serverHost, '::1') !== false) ||
    preg_match('/^(192\.168|10\.|172\.(1[6-9]|2\d|3[01]))\./', $serverHost);

$isApk = $isLocalDev ||
    (strpos($userAgent, 'IntanElyuAPK') !== false) ||
    (strpos($userAgent, 'Capacitor') !== false) ||
    isset($_GET['app']) ||
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strpos($_SERVER['HTTP_X_REQUESTED_WITH'], 'com.intan.elyu') !== false);

// WEB BROWSER PROTECTION:
// If accessed via a normal web browser (like Brave, Chrome, Safari, Edge) and NOT inside the Android APK:
// The user flow is strictly: Search Intan Elyu -> View Website / Download Page -> Scan QR / Download APK -> Open Mobile App.
// Web browsers are BLOCKED from accessing mobile app screens (?view=splash, ?view=auth, etc.)
// and redirected immediately to https://app.intan-elyu.online/?view=download!
if (!$isApk) {
    $reqView = $_GET['view'] ?? '';
    // If not on the download view, check client-side hash for Google OAuth callback first
    if ($reqView !== 'download') {
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connecting to Intan Elyu...</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 20px; background: linear-gradient(135deg, #0b1a3d 0%, #1e3a8a 100%); color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; text-align: center; }
        .card { background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 24px; padding: 36px 24px; width: 100%; max-width: 360px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4); }
        .spinner { width: 44px; height: 44px; border: 4px solid rgba(255, 255, 255, 0.2); border-top-color: #38bdf8; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 20px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        h2 { font-size: 20px; font-weight: 700; margin: 0 0 10px; color: #ffffff; }
        p { font-size: 14px; color: rgba(255, 255, 255, 0.8); margin: 0 0 24px; line-height: 1.5; }
        .btn-app { display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #ffffff; color: #1e3a8a; font-weight: 700; font-size: 15px; padding: 14px 28px; border-radius: 100px; text-decoration: none; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25); }
    </style>
</head>
<body>
    <div class="card" id="redir-card">
        <div class="spinner"></div>
        <h2 id="redir-title">Connecting to Intan Elyu...</h2>
        <p id="redir-desc">Completing Google Sign-In and returning to the mobile app.</p>
        <a id="open-app-btn" href="#" class="btn-app" style="display:none;">Open App Now</a>
    </div>
    <script>
    (function() {
        var h = window.location.hash || "";
        var s = window.location.search || "";
        var isOAuth = (h.indexOf("access_token=") !== -1 || h.indexOf("id_token=") !== -1 || h.indexOf("error=") !== -1 || s.indexOf("access_token=") !== -1 || s.indexOf("id_token=") !== -1);
        
        if (isOAuth) {
            var isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
            var targetPayload = h || s;
            var cleanParams = targetPayload.replace(/^[#?]/, "");
            var appUrl = "intanelyu://google-auth?" + cleanParams;
            var intentUrl = "intent://google-auth?" + cleanParams + "#Intent;scheme=intanelyu;package=com.intan.elyu;end";

            var btn = document.getElementById("open-app-btn");
            if (btn) {
                btn.href = (/Android/i.test(navigator.userAgent)) ? intentUrl : appUrl;
                btn.style.display = "inline-flex";
            }

            if (isMobile) {
                var target = (/Android/i.test(navigator.userAgent)) ? intentUrl : appUrl;
                try {
                    window.location.replace(target);
                } catch(e) {
                    window.location.href = target;
                }
            } else {
                window.location.replace("index.php?view=auth&app=true&" + cleanParams);
            }
        } else {
            window.location.replace("index.php?view=download");
        }
    })();
    </script>
    <noscript><meta http-equiv="refresh" content="0;url=index.php?view=download"></noscript>
</body>
</html>
<?php
        exit;
    }
}

// Inside the APK:
$rawView = 'splash';
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
    <!-- Immediate Google OAuth Token Interceptor & Native App Handoff -->
    <script>
        (function () {
            var h = window.location.hash || '';
            var s = window.location.search || '';
            var payload = h || s;
            var hasOAuthParam = (payload.indexOf('access_token=') !== -1 || payload.indexOf('id_token=') !== -1 || payload.indexOf('error=') !== -1);
            
            var isInsideNativeApk = Boolean(
                (window.Capacitor && typeof window.Capacitor.isNativePlatform === 'function' && window.Capacitor.isNativePlatform()) ||
                (navigator.userAgent && navigator.userAgent.indexOf('IntanElyuAPK') !== -1)
            );

            // If this is Chrome Custom Tabs / external browser returning with OAuth token:
            if (!isInsideNativeApk && hasOAuthParam) {
                var cleanParams = payload.replace(/^[#?]/, '');
                var appUrl = "intanelyu://google-auth?" + cleanParams;
                var intentUrl = "intent://google-auth?" + cleanParams + "#Intent;scheme=intanelyu;package=com.intan.elyu;end";
                
                try {
                    if (/Android/i.test(navigator.userAgent)) {
                        window.location.replace(intentUrl);
                    } else {
                        window.location.replace(appUrl);
                    }
                } catch(e) {
                    window.location.href = appUrl;
                }
                
                document.documentElement.innerHTML = '<head><meta name="viewport" content="width=device-width,initial-scale=1"><title>Returning to Intan Elyu...</title><style>*{box-sizing:border-box}body{margin:0;padding:24px;min-height:100vh;background:linear-gradient(135deg,#0b1a3d 0%,#1e3a8a 100%);display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:#fff;text-align:center}.card{background:rgba(255,255,255,0.08);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.18);border-radius:24px;padding:36px 24px;max-width:340px;width:100%;box-shadow:0 20px 40px rgba(0,0,0,0.4)}.spinner{width:44px;height:44px;border:4px solid rgba(255,255,255,0.2);border-top-color:#38bdf8;border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 20px}@keyframes spin{to{transform:rotate(360deg)}}h2{font-size:20px;margin:0 0 8px;font-weight:700}p{font-size:14px;color:rgba(255,255,255,0.8);margin:0 0 24px}.btn{display:inline-flex;align-items:center;justify-content:center;background:#fff;color:#1e3a8a;font-weight:800;font-size:15px;padding:14px 28px;border-radius:100px;text-decoration:none;box-shadow:0 8px 24px rgba(0,0,0,0.3)}</style></head><body><div class="card"><div class="spinner"></div><h2>Returning to App...</h2><p>Authentication complete! Returning to the Intan Elyu app.</p><a href="' + (/Android/i.test(navigator.userAgent) ? intentUrl : appUrl) + '" class="btn">Open Intan Elyu App</a></div></body>';
                
                setTimeout(function() { window.location.href = (/Android/i.test(navigator.userAgent) ? intentUrl : appUrl); }, 400);
                return;
            }

            var isOAuthPending = false;
            try {
                isOAuthPending = (window.sessionStorage && sessionStorage.getItem('intan_elyu_google_oauth_pending') === 'true');
            } catch(e) {}

            if (hasOAuthParam || isOAuthPending) {
                window._isProcessingGoogleOAuth = true;
                window._googleOAuthHash = h;
                window._googleOAuthSearch = s;
                var st = document.createElement('style');
                st.id = 'oauth-splash-blocker';
                st.textContent = '#splash-main, #global-cloud-container, .splash-container, .wave-wrapper, .splash-tagline, .loading-dots, .splash-offline-pill { display: none !important; opacity: 0 !important; visibility: hidden !important; pointer-events: none !important; }';
                (document.head || document.documentElement).appendChild(st);
            }
        })();
    </script>
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
    <meta name="description"
        content="Official smart tourism mobile platform and portal of the Provincial Government of La Union (PGLU). Discover 20 municipalities, attractions, surf spots, discounts, travel fares, and download the Intan Elyu mobile app.">
    <meta name="keywords"
        content="Intan Elyu, Intan Elyu mobile, Intan Elyu app, Intan Elyu download, Intan Elyu APK, La Union tourism, Elyu, San Juan surfing, PGLU, LUPTO, PICTO, Tangadan Falls, Balaoan Immuki Island, Luna Pebble Beach, Bauang grapes, La Union travel guide, mobile tourism app, Northern Luzon">
    <meta name="author" content="Provincial Government of La Union (PGLU)">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
    <link rel="canonical" href="https://app.intan-elyu.online/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Intan Elyu">
    <meta property="og:url" content="https://app.intan-elyu.online/">
    <meta property="og:title" content="Intan Elyu — Official Tourism Portal & Mobile App | Province of La Union">
    <meta property="og:description"
        content="Discover, explore, and experience the whole of La Union with Intan Elyu. Plan itineraries, discover 20 municipalities, view tourist spots, discounts, and earn gamified rewards.">
    <meta property="og:image" content="https://app.intan-elyu.online/assets/img/logo.png">
    <meta property="og:locale" content="en_PH">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Intan Elyu — Official Tourism Portal & Mobile App | Province of La Union">
    <meta name="twitter:description"
        content="Discover, explore, and experience the whole of La Union with Intan Elyu. Plan itineraries, discover 20 municipalities, view tourist spots, discounts, and earn gamified rewards.">
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


    <script src="assets/js/capacitor/capacitor.js?v=<?= time() ?>"></script>
    <script>
        function isLocalDevHost(hn) {
            if (!hn) return false;
            if (hn === 'localhost' || hn === '127.0.0.1' || hn === '::1') return true;
            return /^192\.168\.\d+\.\d+$/.test(hn) ||
                   /^10\.\d+\.\d+\.\d+$/.test(hn) ||
                   /^172\.(1[6-9]|2\d|3[01])\.\d+\.\d+$/.test(hn);
        }

        const isNativeMobile = Boolean(
            (window.Capacitor && typeof window.Capacitor.isNativePlatform === 'function' && window.Capacitor.isNativePlatform()) ||
            (navigator.userAgent && navigator.userAgent.includes('IntanElyuAPK')) ||
            window.location.protocol === 'capacitor:' ||
            window.location.protocol === 'file:'
        );

        if (isNativeMobile) {
            window.backendUrl = window.BACKEND_URL || 'https://api.intan-elyu.online';
        } else if (window.location.pathname.includes('/Intan-Elyu-Tourism-Management-System/')) {
            window.backendUrl = window.location.protocol + '//' + window.location.host + '/Intan-Elyu-Tourism-Management-System/backend/public';
        } else if (window.location.port === '3000') {
            window.backendUrl = window.location.protocol + '//' + window.location.hostname + ':8000';
        } else if (isLocalDevHost(window.location.hostname)) {
            window.backendUrl = window.location.protocol + '//' + window.location.hostname + ':8000';
        } else if (window.location.hostname.endsWith('intan-elyu.online')) {
            window.backendUrl = 'https://api.intan-elyu.online';
        } else {
            window.backendUrl = window.BACKEND_URL || (window.location.origin.includes('app.') ? window.location.origin.replace('app.', 'api.') : 'https://api.intan-elyu.online');
        }
        window.GOOGLE_CLIENT_ID = '620598190857-37a0ucobfd4b3rct7ofti8rtvl3qt884.apps.googleusercontent.com';
    </script>
    <script src="assets/js/main.js?v=<?= time() ?>"></script>

    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <!-- Component Styles -->
    <link rel="stylesheet" href="assets/css/components/header.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/components/bottom_nav.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/components/modals.css?v=<?= time() ?>">
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
    <link rel="stylesheet" href="assets/css/views/puzzles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/discount.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/trip_map.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/saved_trips.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/saved_places.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/ar_checkin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/trending.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/views/user_manual.css?v=<?= time() ?>">
</head>

<body data-view="<?= htmlspecialchars($view) ?>">
    <!-- Ambient Offline Mode Status Banner -->
    <div id="elyu-offline-pill" class="elyu-offline-pill hidden">
        <i class="fa-solid fa-cloud-slash" id="elyu-offline-pill-icon" style="color: #f59e0b; font-size: 11px;"></i>
        <span id="elyu-offline-pill-text">Offline Mode &bull; Showing saved data</span>
    </div>

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

</body>

</html>