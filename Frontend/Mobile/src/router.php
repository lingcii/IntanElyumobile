<?php
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

$backendUrl = 'https://intanelyu-production.up.railway.app';
$cacheDir = __DIR__ . '/assets/img/upload_image';

function serveCachedImage($cachePath) {
    if (!file_exists($cachePath)) return false;
    $ext = strtolower(pathinfo($cachePath, PATHINFO_EXTENSION));
    $mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
    header('Content-Type: ' . ($mimeMap[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=86400');
    readfile($cachePath);
    return true;
}

function proxyAndCacheImage($sourceUrl, $cachePath, $cacheDir) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sourceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $responseHeaders = substr($response, 0, $headerSize);
    $responseBody = substr($response, $headerSize);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($responseBody)) {
        if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);
        file_put_contents($cachePath, $responseBody);
    }

    http_response_code($httpCode);
    foreach (explode("\r\n", $responseHeaders) as $header) {
        if (stripos($header, 'Content-Type:') === 0 || stripos($header, 'content-type:') === 0) {
            header($header);
        }
    }
    echo $responseBody;
    return true;
}

function cacheKeyFromPath($path) {
    $relative = ltrim($path, '/');
    $safe = str_replace(['/', '\\'], '_', $relative);
    return $safe;
}

// Serve cached images from /storage/ (direct Laravel storage link)
if (strpos($path, '/storage/') === 0) {
    $cacheKey = cacheKeyFromPath($path);
    $cachePath = $cacheDir . '/' . $cacheKey;
    if (serveCachedImage($cachePath)) return true;
    return proxyAndCacheImage($backendUrl . $path, $cachePath, $cacheDir);
}

// Serve and cache images from /api/image/ (backend image route)
if (strpos($path, '/api/image/') === 0) {
    $cacheKey = cacheKeyFromPath($path);
    $cachePath = $cacheDir . '/' . $cacheKey;
    if (serveCachedImage($cachePath)) return true;
    return proxyAndCacheImage($backendUrl . $path, $cachePath, $cacheDir);
}

// Proxy API requests to Laravel backend
if (strpos($path, '/api/') === 0) {
    $target = $backendUrl . $uri;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $target);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $headers = getallheaders();
    $curlHeaders = [];
    foreach ($headers as $key => $value) {
        if (strtolower($key) !== 'host') {
            $curlHeaders[] = "$key: $value";
        }
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
        $body = file_get_contents('php://input');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $responseHeaders = substr($response, 0, $headerSize);
    $responseBody = substr($response, $headerSize);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    http_response_code($httpCode);
    foreach (explode("\r\n", $responseHeaders) as $header) {
        if (stripos($header, 'Content-Type:') === 0 || stripos($header, 'content-type:') === 0) {
            header($header);
        }
    }
    echo $responseBody;
    return true;
}

// Pre-cache all destination images
if ($path === '/cache-all-images') {
    header('Content-Type: application/json');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $backendUrl . '/api/public/map');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $json = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($json, true);
    $cached = 0;
    $errors = 0;

    if ($data && isset($data['destinations'])) {
        foreach ($data['destinations'] as $dest) {
            $photoUrl = $dest['photo_url'] ?? ($dest['image'] ?? null);
            if (empty($photoUrl)) continue;
            if (strpos($photoUrl, 'http') === 0) continue;

            $cacheKey = cacheKeyFromPath('api/image/' . $photoUrl);
            $cachePath = $cacheDir . '/' . $cacheKey;

            if (file_exists($cachePath)) {
                $cached++;
                continue;
            }

            $img = @file_get_contents($backendUrl . '/api/image/' . $photoUrl);
            if ($img !== false) {
                if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);
                file_put_contents($cachePath, $img);
                $cached++;
            } else {
                $errors++;
            }
        }
    }

    echo json_encode(['cached' => $cached, 'errors' => $errors]);
    return true;
}

return false;
