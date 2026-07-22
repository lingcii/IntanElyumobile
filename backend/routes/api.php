<?php

use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Tourist\DashboardController as TouristDashboardController;
use App\Http\Controllers\Tourist\ProfileController as TouristProfileController;
use App\Http\Controllers\Tourist\FavoriteController;
use App\Http\Controllers\Tourist\ItineraryController;
use App\Http\Controllers\Tourist\ItineraryItemController;
use App\Http\Controllers\Tourist\NotificationController;
use App\Http\Controllers\MerchandiseController;
use App\Http\Controllers\Tourist\FeedbackController;
use App\Http\Controllers\Tourist\PointsController;
use App\Models\TouristSpot;
use Illuminate\Support\Facades\Route;

// Catch-all OPTIONS route to guarantee CORS headers on preflight requests
Route::options('/{any}', function (\Illuminate\Http\Request $request) {
    $origin = $request->header('Origin') ?: '*';
    return response('', 200)
        ->header('Access-Control-Allow-Origin', $origin)
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, Accept, Origin')
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Max-Age', '86400');
})->where('any', '.*');

// Legacy route to serve images from storage (tourist_spots, municipalities, etc.)
Route::get('/image/{file}', function ($file) {
    if (!$file) abort(404);
    $base = storage_path('app/public');
    
    $paths = [
        $base . '/tourist_spots/' . $file,
        $base . '/' . $file,
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            return response()->file($path);
        }
    }
    
    abort(404);
})->where('file', '.+');

// Backward-compatible route for legacy /api/serve-image.php?file=... URLs
Route::get('/serve-image.php', function (\Illuminate\Http\Request $request) {
    $file = $request->query('file');
    if (!$file) abort(404);
    $base = storage_path('app/public');
    
    $paths = [
        $base . '/tourist_spots/' . $file,
        $base . '/' . $file,
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            return response()->file($path);
        }
    }
    
    abort(404);
});

// ─────────────────────────────────────────────────────────────────────────────
//  Auth (public)
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    // ── Rate-limited: brute-force / credential-stuffing protection ──────────
    // Max 5 attempts per IP per 60 seconds on login & register.
    Route::middleware('auth.throttle:5,60')->group(function () {
        Route::post('/login',    [LoginController::class,    'login']);
        Route::post('/register', [RegisterController::class, 'register']);
    });

    // Not rate-limited — these can't be brute-forced in any meaningful way
    Route::post('/logout',   [LogoutController::class,  'logout']);
    Route::get('/check',     [SessionController::class, 'check']);
    Route::post('/google',   [LoginController::class,   'googleLogin']);
    Route::post('/forgot-password', [LoginController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password',  [LoginController::class, 'resetPassword']);

    Route::get('/google/redirect', function () {
        return response()->json([
            'success' => false,
            'message' => 'Google Authentication is not yet configured on the backend.'
        ], 501);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
//  ADMIN API (spot photo upload & management) — Protected by auth + role check
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('admin')->middleware('tourist.auth')->group(function () {
    Route::post('/spots/{id}/photo', function (\Illuminate\Http\Request $request, $id) {
        // Only allow admin-level roles (picto, lupto, municipal MTOs)
        $user = $request->user();
        if ($user->role === 'tourist') {
            return response()->json(['error' => 'Forbidden: admin access required.'], 403);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240'
        ]);

        $spot = TouristSpot::findOrFail($id);
        $path = $request->file('photo')->store('tourist_spots', 'public');
        $fullUrl = asset('storage/' . $path);

        $spot->update(['photo_url' => 'storage/' . $path]);

        // Clear public map cache so mobile app gets the new photo immediately
        \Illuminate\Support\Facades\Cache::forget('map:public:spots');

        return response()->json([
            'success' => true,
            'message' => 'Photo uploaded successfully!',
            'photo_url' => $fullUrl,
            'spot' => $spot
        ]);
    });

    Route::post('/spots', function (\Illuminate\Http\Request $request) {
        // Only allow admin-level roles
        $user = $request->user();
        if ($user->role === 'tourist') {
            return response()->json(['error' => 'Forbidden: admin access required.'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string',
            'municipality_id' => 'required|integer',
            'category' => 'required|string',
            'entrance_fee' => 'nullable|numeric',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240'
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('tourist_spots', 'public');
            $data['photo_url'] = 'storage/' . $path;
        }

        $spot = TouristSpot::create($data);
        \Illuminate\Support\Facades\Cache::forget('map:public:spots');

        return response()->json([
            'success' => true,
            'message' => 'Tourist spot created successfully!',
            'spot' => $spot
        ]);
    });
});

// Top-level aliases for backward-compatible admin website views
Route::get('/municipalities', [MapController::class, 'publicMunicipalities']);
Route::get('/tourist-spots',  [MapController::class, 'publicMapData']);

// ─────────────────────────────────────────────────────────────────────────────
//  PUBLIC routes (no auth required) — for mobile app unauthenticated features
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('public')->group(function () {
    Route::get('/map',            [MapController::class, 'publicMapData']);
    Route::get('/test', function() { return response()->json(['message' => 'test']); });
    Route::get('/fares',          [MapController::class, 'publicFares']);
    Route::get('/municipalities', [MapController::class, 'publicMunicipalities']);
    Route::get('/leaderboard',    [LeaderboardController::class, 'index']);
    Route::get('/feedback',       [FeedbackController::class, 'index']);
});

// ─────────────────────────────────────────────────────────────────────────────
//  TOURIST (mobile app — Bearer token auth)
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('tourist')->middleware('tourist.auth')->group(function () {
    Route::get('/dashboard', [TouristDashboardController::class, 'index']);
    Route::get('/profile', [TouristProfileController::class, 'show']);
    Route::post('/profile', [TouristProfileController::class, 'update']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);

    Route::post('/destinations/{id}/favorite', [FavoriteController::class, 'toggle']);
    Route::post('/destinations/{id}/rate', function (Illuminate\Http\Request $request, int $id) {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);
        $spot = TouristSpot::findOrFail($id);
        $spot->update(['rating' => $request->rating]);
        return response()->json(['message' => 'Rating submitted!']);
    });

    Route::get('/itineraries',              [ItineraryController::class, 'index']);
    Route::get('/itineraries/{id}',         [ItineraryController::class, 'show']);
    Route::post('/itineraries',             [ItineraryController::class, 'store']);
    Route::put('/itineraries/{id}',         [ItineraryController::class, 'update']);
    Route::delete('/itineraries/{id}',      [ItineraryController::class, 'destroy']);
    Route::patch('/itineraries/{id}/complete', [ItineraryController::class, 'markCompleted']);

    Route::patch('/itineraries/items/{id}/visit', [ItineraryItemController::class, 'visit']);
    Route::post('/itineraries/items/{id}/visit',  [ItineraryItemController::class, 'visit']);

    Route::get('/merch', [MerchandiseController::class, 'index']);
    Route::post('/merch/reserve', [MerchandiseController::class, 'reserve']);

    Route::get('/notifications',         [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all',  [NotificationController::class, 'markAllRead']);

    // Site Testimonies & Policy Recommendations
    Route::get('/feedback', [FeedbackController::class, 'index']);
    Route::post('/feedback', [FeedbackController::class, 'store']);

    // Points & Vouchers
    Route::get('/points/balance', [PointsController::class, 'getBalance']);
    Route::post('/points/puzzle', [PointsController::class, 'awardPuzzlePoints']);
    Route::post('/points/trivia', [PointsController::class, 'awardTriviaPoints']);
    Route::post('/points/minigame', [PointsController::class, 'awardMiniGamePoints']);
    Route::post('/points/redeem', [PointsController::class, 'redeem']);
});
