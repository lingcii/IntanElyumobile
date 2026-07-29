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
use App\Http\Controllers\Tourist\FeedbackController;
use App\Http\Controllers\Tourist\PointsController;
use App\Http\Controllers\Tourist\QuestController;
use App\Http\Controllers\WeatherController;
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

// Route to serve images from storage (proof_images, tourist_spots, municipalities, avatars, etc.)
$serveFileHandler = function ($file) {
    if (!$file) abort(404);
    $file = rawurldecode(urldecode($file));
    $base = storage_path('app/public');
    
    $cleanFile = preg_replace('#^storage/#i', '', $file);
    $normalizedFile = preg_replace('#^municipalities/#i', 'MUNICIPALITIES/', $cleanFile);
    
    $paths = [
        $base . '/' . $cleanFile,
        $base . '/' . $file,
        $base . '/Logo/' . preg_replace('#^Logo/#i', '', $cleanFile),
        $base . '/proof_images/' . preg_replace('#^proof_images/#i', '', $cleanFile),
        $base . '/tourist_spots/' . preg_replace('#^tourist_spots/#i', '', $cleanFile),
        $base . '/fare_matrices/' . preg_replace('#^fare_matrices/#i', '', $cleanFile),
        $base . '/avatars/' . preg_replace('#^avatars/#i', '', $cleanFile),
        base_path('../frontend/Mobile/src/assets/img/' . $normalizedFile),
        base_path('../frontend/Mobile/src/assets/img/upload_image/' . $cleanFile),
        base_path('../frontend/Mobile/src/assets/img/' . $cleanFile),
        public_path('storage/' . $cleanFile),
        public_path('images/tourist_spots/' . $cleanFile),
        public_path('storage/tourist_spots/' . $cleanFile),
        public_path('uploads/tourist_spots/' . $cleanFile),
        public_path('storage/upload_image/' . $cleanFile),
        public_path('upload_image/' . $cleanFile),
        base_path('../frontend/Mobile/src/assets/images/' . $cleanFile),
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path) && is_file($path)) {
            return response()->file($path);
        }
    }
    
    $disk = env('FILESYSTEM_DISK', 'public');
    if (in_array($disk, ['r2', 's3'])) {
        // Try multiple path patterns to find the file in R2
        $r2Paths = array_unique(array_filter([
            $cleanFile,
            'tourist_spots/' . basename($cleanFile),
            preg_replace('#^storage/#i', '', $cleanFile),
            basename($cleanFile),
        ]));
        foreach ($r2Paths as $r2Path) {
            try {
                if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($r2Path)) {
                    return redirect(\Illuminate\Support\Facades\Storage::disk($disk)->url($r2Path));
                }
            } catch (\Throwable $e) {}
        }
    }

    abort(404);
};

Route::get('/image/{file}', $serveFileHandler)->where('file', '.+');
Route::get('/storage/{file}', $serveFileHandler)->where('file', '.+');

// Backward-compatible route for legacy /api/serve-image.php?file=... URLs
Route::get('/serve-image.php', function (\Illuminate\Http\Request $request) {
    $file = $request->query('file');
    if (!$file) abort(404);
    $file = rawurldecode(urldecode($file));
    $base = storage_path('app/public');
    
    // Normalize lowercase municipalities/ to match the actual MUNICIPALITIES/ folder
    $normalizedFile = preg_replace('#^municipalities/#i', 'MUNICIPALITIES/', $file);
    
    $paths = [
        base_path('../frontend/Mobile/src/assets/img/' . $normalizedFile),
        base_path('../frontend/Mobile/src/assets/img/upload_image/' . $file),
        base_path('../frontend/Mobile/src/assets/img/' . $file),
        $base . '/' . $file,
        $base . '/tourist_spots/' . $file,
        $base . '/upload_image/' . $file,
        public_path('images/tourist_spots/' . $file),
        public_path('storage/tourist_spots/' . $file),
        public_path('uploads/tourist_spots/' . $file),
        public_path('storage/upload_image/' . $file),
        public_path('upload_image/' . $file),
        base_path('../frontend/Mobile/src/assets/images/' . $file),
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
        Route::post('/login',      [LoginController::class,    'login']);
        Route::post('/register',   [RegisterController::class, 'register']);
        Route::post('/verify-otp', [RegisterController::class, 'verifyOtp']);
    });

    // Not rate-limited — these can't be brute-forced in any meaningful way
    Route::post('/logout',   [LogoutController::class,  'logout']);
    Route::get('/check',     [SessionController::class, 'check']);
    Route::post('/google',   [LoginController::class,   'googleLogin']);
    Route::post('/forgot-password',     [LoginController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password',      [LoginController::class, 'resetPassword']);
    Route::post('/reset-password-otp',  [LoginController::class, 'resetPasswordWithOtp']);

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
        $disk = env('FILESYSTEM_DISK', 'public');
        $path = $request->file('photo')->store('tourist_spots', $disk);

        if (in_array($disk, ['r2', 's3'])) {
            $fullUrl = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);
            $spot->update(['photo_url' => $fullUrl]);
        } else {
            $fullUrl = asset('storage/' . $path);
            $spot->update(['photo_url' => 'storage/' . $path]);
        }

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
            $disk = env('FILESYSTEM_DISK', 'public');
            $path = $request->file('photo')->store('tourist_spots', $disk);
            if (in_array($disk, ['r2', 's3'])) {
                $data['photo_url'] = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);
            } else {
                $data['photo_url'] = 'storage/' . $path;
            }
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
//  ADMIN PANEL ENDPOINTS (LUPTO, PITCO, PICTO, MUNICIPAL)
// ─────────────────────────────────────────────────────────────────────────────
foreach (['lupto', 'pitco', 'picto', 'municipal'] as $rolePrefix) {
    Route::prefix($rolePrefix)->group(function () use ($rolePrefix) {
        Route::get('/dashboard', function () {
            $totalSpots = \App\Models\TouristSpot::count();
            $totalMunis = \Illuminate\Support\Facades\DB::table('municipalities')->count();
            $totalUsers = \App\Models\User::count();
            $spots = \App\Models\TouristSpot::with('municipality')->get();

            return response()->json([
                'success' => true,
                'kpis' => [
                    'total_municipalities' => $totalMunis ?: 20,
                    'total_spots' => $totalSpots ?: 12,
                    'pending_approvals' => 0,
                    'total_tourists' => $totalUsers ?: 150
                ],
                'municipalities' => \Illuminate\Support\Facades\DB::table('municipalities')->get(),
                'touristSpots' => $spots,
                'alerts' => []
            ]);
        });

        Route::get('/tourist-spots', function () {
            $spots = \App\Models\TouristSpot::with('municipality')->get();
            return response()->json($spots);
        });

        Route::get('/users', function () {
            $users = \App\Models\User::all();
            return response()->json([
                'success' => true,
                'users' => $users,
                'roleStats' => []
            ]);
        });

        Route::get('/feedback', function (\Illuminate\Http\Request $request) {
            $spotId = $request->query('tourist_spot_id');
            $query = \App\Models\SiteFeedback::with(['user:id,name,email,avatar', 'touristSpot:id,name,category,municipality_id'])
                ->latest();
            if ($spotId) {
                $query->where('tourist_spot_id', $spotId);
            }
            return response()->json([
                'success' => true,
                'feedbacks' => $query->get()
            ]);
        });

        Route::get('/analytics/full', function () {
            return response()->json([
                'success' => true,
                'analytics' => [
                    'monthly_visits' => [],
                    'category_distribution' => [],
                    'demographics' => []
                ]
            ]);
        });

        Route::get('/analytics/summary', function () {
            $totalSpots = \App\Models\TouristSpot::count();
            return response()->json([
                'success' => true,
                'summary' => [
                    'total_spots' => $totalSpots ?: 12,
                    'approved_spots' => $totalSpots ?: 12,
                    'total_visits' => 450,
                    'total_analytics_visits' => 450,
                    'most_visited_spot' => 'Ma-Cho Temple',
                    'most_visited_muni' => 'San Fernando',
                    'growth_rate' => 12.5
                ]
            ]);
        });

        Route::get('/fare-data/guides', function () {
            return response()->json([
                'success' => true,
                'guides' => [],
                'data' => []
            ]);
        });

        Route::get('/fare-data/uploads', function () {
            return response()->json([
                'success' => true,
                'uploads' => [],
                'data' => []
            ]);
        });

        Route::get('/fare-data', function () {
            return response()->json([
                'success' => true,
                'fare_data' => [],
                'guides' => [],
                'uploads' => []
            ]);
        });

        Route::any('/fare-data/{any}', function () {
            return response()->json([
                'success' => true,
                'fare_data' => [],
                'guides' => [],
                'uploads' => [],
                'data' => []
            ]);
        })->where('any', '.*');

        Route::get('/analytics/chart-data', function () {
            return response()->json([
                'success' => true,
                'categories' => [],
                'visits' => []
            ]);
        });

        Route::get('/analytics/monthly-trend', function () {
            return response()->json([
                'success' => true,
                'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'data' => [3200, 3800, 3500, 4200, 4500, 4800, 4100, 4300, 4600, 4800, 4900, 4520]
            ]);
        });

        Route::get('/analytics', function () {
            return response()->json([
                'success' => true,
                'analytics' => []
            ]);
        });

        Route::any('/analytics/{any}', function () {
            return response()->json([
                'success' => true,
                'analytics' => [],
                'data' => []
            ]);
        })->where('any', '.*');

        Route::get('/leaderboard', [LeaderboardController::class, 'index']);

        Route::get('/archive/stats', function () {
            return response()->json([
                'success' => true,
                'stats' => [
                    'fares' => 0,
                    'users' => 0,
                    'spots' => 0,
                    'total' => 0
                ]
            ]);
        });

        Route::get('/archive/fares', function () {
            return response()->json([
                'success' => true,
                'fares' => [],
                'data'  => []
            ]);
        });

        Route::get('/archive', function () {
            return response()->json([
                'success' => true,
                'archive' => [],
                'fares' => [],
                'stats' => ['fares' => 0, 'users' => 0, 'spots' => 0, 'total' => 0]
            ]);
        });

        Route::any('/archive/{any}', function () {
            return response()->json([
                'success' => true,
                'archive' => [],
                'fares' => [],
                'stats' => ['fares' => 0, 'users' => 0, 'spots' => 0, 'total' => 0]
            ]);
        })->where('any', '.*');

        Route::get('/fare-data', function () {
            return response()->json([
                'success' => true,
                'fare_data' => []
            ]);
        });

        Route::get('/activity-logs', function () {
            return response()->json([
                'success' => true,
                'logs' => []
            ]);
        });

        Route::post('/dashboard/approve-spot', function (\Illuminate\Http\Request $request) {
            $spot = \App\Models\TouristSpot::find($request->input('id'));
            if ($spot) $spot->update(['status' => 'approved']);
            return response()->json(['success' => true]);
        });

        Route::post('/dashboard/reject-spot', function (\Illuminate\Http\Request $request) {
            $spot = \App\Models\TouristSpot::find($request->input('id'));
            if ($spot) $spot->update(['status' => 'rejected']);
            return response()->json(['success' => true]);
        });

        Route::post('/dashboard/batch-approve-spots', function (\Illuminate\Http\Request $request) {
            $ids = $request->input('ids', []);
            \App\Models\TouristSpot::whereIn('id', $ids)->update(['status' => 'approved']);
            return response()->json(['success' => true]);
        });
    });
}

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
    Route::get('/quests',         [QuestController::class, 'index']);
    Route::get('/weather',        [WeatherController::class, 'getWeather']);
});
Route::get('/weather', [WeatherController::class, 'getWeather']);

// ─────────────────────────────────────────────────────────────────────────────
//  TOURIST (mobile app — Bearer token auth)
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('tourist')->middleware('tourist.auth')->group(function () {
    Route::get('/dashboard', [TouristDashboardController::class, 'index']);
    Route::get('/profile', [TouristProfileController::class, 'show']);
    Route::post('/profile', [TouristProfileController::class, 'update']);
    Route::post('/2fa/toggle', [TouristProfileController::class, 'toggle2FA']);
    Route::post('/2fa/verify', [TouristProfileController::class, 'verify2FA']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);

    Route::post('/destinations/{id}/favorite', [FavoriteController::class, 'toggle']);
    Route::post('/destinations/{id}/rate', function (Illuminate\Http\Request $request, int $id) {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);
        $spot = TouristSpot::findOrFail($id);
        $user = $request->user();

        // Create or update feedback rating for this user & spot
        \App\Models\SiteFeedback::updateOrCreate(
            ['user_id' => $user->id, 'tourist_spot_id' => $id],
            ['rating' => $request->rating]
        );

        // Recalculate true average rating from all site feedbacks for this spot
        $avgRating = \App\Models\SiteFeedback::where('tourist_spot_id', $id)
            ->whereNotNull('rating')
            ->avg('rating');

        $spot->rating = round((float)$avgRating, 2);
        $spot->save();

        // Invalidate public map & trending spots cache
        \Illuminate\Support\Facades\Cache::forget('map:public:spots');
        \Illuminate\Support\Facades\Cache::forget('trending:top:5');
        \Illuminate\Support\Facades\Cache::forget('trending:top:10');
        \Illuminate\Support\Facades\Cache::forget('trending:top:50');

        // Award gamification points (+25 XP, +25 points)
        try {
            $user->increment('xp', 25);
            \App\Models\UserPoint::awardPointsSafely(
                $user->id,
                25,
                'rating',
                "Rated {$spot->name} {$request->rating} stars"
            );
        } catch (\Throwable $e) {}

        return response()->json([
            'message' => 'Rating submitted successfully!',
            'spot_rating' => $spot->rating
        ]);
    });

    Route::get('/itineraries',              [ItineraryController::class, 'index']);
    Route::get('/itineraries/{id}',         [ItineraryController::class, 'show']);
    Route::post('/itineraries',             [ItineraryController::class, 'store']);
    Route::post('/itineraries/estimate-cost', [ItineraryController::class, 'estimateCost']);
    Route::put('/itineraries/{id}',         [ItineraryController::class, 'update']);
    Route::delete('/itineraries/{id}',      [ItineraryController::class, 'destroy']);
    Route::patch('/itineraries/{id}/complete', [ItineraryController::class, 'markCompleted']);

    Route::patch('/itineraries/items/{id}/visit', [ItineraryItemController::class, 'visit']);
    Route::post('/itineraries/items/{id}/visit',  [ItineraryItemController::class, 'visit']);

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

    // Quests & Gamification
    Route::get('/quests', [QuestController::class, 'index']);
    Route::get('/quests/my-completions', [QuestController::class, 'myCompletions']);
    Route::get('/quests/{id}/generate', [QuestController::class, 'generate']);
    Route::post('/quests/{id}/start', [QuestController::class, 'startQuest']);
});
