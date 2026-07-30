<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes - Serves Mobile Web Application & Health Check
|--------------------------------------------------------------------------
*/

// Health check endpoint for Railway monitoring
Route::get('/up', function () {
    return response('OK', 200);
});

// Serve Mobile App Static Assets (CSS, JS, Images, Fonts)
Route::get('/assets/{path}', function ($path) {
    $possiblePaths = [
        base_path('../frontend/Mobile/src/assets/' . $path),
        base_path('public/assets/' . $path),
    ];

    foreach ($possiblePaths as $filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            $mime = mime_content_type($filePath) ?: 'text/plain';
            if (str_ends_with($filePath, '.css'))   $mime = 'text/css; charset=utf-8';
            if (str_ends_with($filePath, '.js'))    $mime = 'application/javascript; charset=utf-8';
            if (str_ends_with($filePath, '.svg'))   $mime = 'image/svg+xml';
            if (str_ends_with($filePath, '.json'))  $mime = 'application/json';
            if (str_ends_with($filePath, '.png'))   $mime = 'image/png';
            if (str_ends_with($filePath, '.jpg') || str_ends_with($filePath, '.jpeg')) $mime = 'image/jpeg';
            if (str_ends_with($filePath, '.woff2')) $mime = 'font/woff2';

            return response()->file($filePath, ['Content-Type' => $mime]);
        }
    }
    return response('Asset not found', 404);
})->where('path', '.*');

// Serve Uploaded Storage Assets (Avatars, Proof Photos, Spot Media)
Route::get('/storage/{path}', function ($path) {
    $possiblePaths = [
        storage_path('app/public/' . $path),
        base_path('public/storage/' . $path),
        base_path('storage/app/public/' . $path),
    ];

    foreach ($possiblePaths as $filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            $mime = mime_content_type($filePath) ?: 'application/octet-stream';
            if (str_ends_with($filePath, '.png'))  $mime = 'image/png';
            if (str_ends_with($filePath, '.jpg') || str_ends_with($filePath, '.jpeg')) $mime = 'image/jpeg';
            if (str_ends_with($filePath, '.webp')) $mime = 'image/webp';
            if (str_ends_with($filePath, '.svg'))  $mime = 'image/svg+xml';

            return response()->file($filePath, ['Content-Type' => $mime]);
        }
    }
    return response('Storage file not found', 404);
})->where('path', '.*');

// Serve Manifest
Route::get('/manifest.json', function () {
    $path = base_path('../frontend/Mobile/src/manifest.json');
    if (file_exists($path)) {
        return response()->file($path, ['Content-Type' => 'application/json']);
    }
    return response()->json(['name' => 'Intan Elyu']);
});

// Helper function to render Mobile PHP App
function renderMobileApp(Request $request)
{
    try {
        $mobileSrcPath = base_path('../frontend/Mobile/src');
        if (!file_exists($mobileSrcPath . '/index.php')) {
            $mobileSrcPath = base_path('public');
        }

        if (file_exists($mobileSrcPath . '/index.php')) {
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                @session_start();
            }

            if ($request->has('view')) {
                $_GET['view'] = $request->query('view');
            }
            if ($request->has('id')) {
                $_GET['id'] = $request->query('id');
            }

            ob_start();
            $oldCwd = getcwd();
            chdir($mobileSrcPath);
            include $mobileSrcPath . '/index.php';
            chdir($oldCwd);
            $output = ob_get_clean();

            return response($output, 200)->header('Content-Type', 'text/html; charset=utf-8');
        }
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error("renderMobileApp Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        return response()->json([
            'error' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }

    return response()->json(['status' => 'online', 'system' => 'Intan-Elyu Tourism API']);
}

// Serve Mobile Web App on Root & App routes
Route::get('/', function (Request $request) {
    return renderMobileApp($request);
});

Route::get('/app', function (Request $request) {
    return renderMobileApp($request);
});
