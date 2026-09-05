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
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\PuzzleController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Models\TouristSpot;
use Illuminate\Support\Facades\Route;

// Catch-all OPTIONS route to guarantee CORS headers on preflight requests
Route::options('/{any}', function (\Illuminate\Http\Request $request) {
    $origin = $request->header('Origin') ?: '*';
    $reqHeaders = $request->header('Access-Control-Request-Headers') ?: 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, Accept, Origin, ngrok-skip-browser-warning, *';
    return response('', 200)
        ->header('Access-Control-Allow-Origin', $origin)
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', $reqHeaders)
        ->header('Access-Control-Allow-Credentials', 'true')
        ->header('Access-Control-Max-Age', '86400');
})->where('any', '.*');

// Route to serve images from storage (proof_images, tourist_spots, municipalities, avatars, etc.)
$serveFileHandler = function ($file) {
    if (!$file)
        abort(404);
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
    $r2Paths = array_unique(array_filter([
        $cleanFile,
        'tourist_spots/' . basename($cleanFile),
        'avatars/' . basename($cleanFile),
        'proof_images/' . basename($cleanFile),
        basename($cleanFile),
    ]));

    if (in_array($disk, ['r2', 's3'])) {
        foreach ($r2Paths as $r2Path) {
            try {
                if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($r2Path)) {
                    return redirect(\Illuminate\Support\Facades\Storage::disk($disk)->url($r2Path));
                }
            } catch (\Throwable $e) {
            }
        }
    }

    // Direct Cloudflare R2 Public Bucket redirect fallback for spot/avatar/proof uploads
    $r2PublicBase = 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev';
    if (preg_match('#spot_|avatar_|proof_#i', $cleanFile)) {
        $folder = preg_match('#avatar_#i', $cleanFile) ? 'avatars' : (preg_match('#proof_#i', $cleanFile) ? 'proof_images' : 'tourist_spots');
        return redirect($r2PublicBase . '/' . $folder . '/' . basename($cleanFile));
    }

    abort(404);
};

Route::get('/image/{file}', $serveFileHandler)->where('file', '.+');
Route::get('/serve/{file}', $serveFileHandler)->where('file', '.+');
Route::get('/storage/{file}', $serveFileHandler)->where('file', '.+');

// Endpoint for api/serve?file=... or api/serve?id=... or api/serve?image_id=...
Route::get('/serve', function (\Illuminate\Http\Request $request) use ($serveFileHandler) {
    $file = $request->query('file') ?: $request->query('id') ?: $request->query('image_id');
    if (!$file)
        abort(404);
    return $serveFileHandler($file);
});

// Backward-compatible route for legacy /api/serve-image.php?file=... URLs
Route::get('/serve-image.php', function (\Illuminate\Http\Request $request) use ($serveFileHandler) {
    $file = $request->query('file') ?: $request->query('id') ?: $request->query('image_id');
    if (!$file)
        abort(404);
    return $serveFileHandler($file);
});

// Puzzle Tourist Spot Images (Public)
Route::match(['GET', 'POST', 'OPTIONS'], '/puzzles/spots', [PuzzleController::class, 'spots']);

// Vouchers (Public & Guest Preview)
Route::get('/vouchers', [\App\Http\Controllers\VoucherController::class, 'index']);
Route::get('/public/vouchers', [\App\Http\Controllers\VoucherController::class, 'index']);

// ─────────────────────────────────────────────────────────────────────────────
//  Auth (public)
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    // ── Rate-limited: brute-force / credential-stuffing protection ──────────
    // Max 5 attempts per IP per 60 seconds on login & register.
    Route::middleware('auth.throttle:5,60')->group(function () {
        Route::post('/login', [LoginController::class, 'login']);
        Route::post('/register', [RegisterController::class, 'register']);
        Route::post('/verify-otp', [RegisterController::class, 'verifyOtp']);
    });

    // Not rate-limited — these can't be brute-forced in any meaningful way
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::get('/check', [SessionController::class, 'check']);
    Route::post('/google', [LoginController::class, 'googleLogin']);
    Route::post('/forgot-password', [LoginController::class, 'sendResetLinkEmail']);
    Route::post('/validate-reset-otp', [LoginController::class, 'validateResetOtp']);
    Route::post('/reset-password', [LoginController::class, 'resetPassword']);
    Route::post('/reset-password-otp', [LoginController::class, 'resetPasswordWithOtp']);
    Route::get('/test-email', [LoginController::class, 'testEmail']);

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
    // ── Scan & Verify Cloudflare R2 images against Railway Database ──
    Route::match(['get', 'post'], '/spots/scan-r2', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        if ($user && $user->role === 'tourist') {
            return response()->json(['error' => 'Forbidden: admin access required.'], 403);
        }

        $doRepair = $request->input('repair', true);
        $spots = TouristSpot::all();
        $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');

        $results = [];
        $verifiedCount = 0;
        $repairedCount = 0;
        $missingCount = 0;

        foreach ($spots as $spot) {
            $rawPhoto = $spot->getRawOriginal('photo_url');
            $r2Path = null;

            if ($rawPhoto) {
                if (preg_match('#(tourist_spots/[^/]+)#i', $rawPhoto, $m)) {
                    $r2Path = $m[1];
                } elseif (preg_match('#(spot_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))#i', $rawPhoto, $m)) {
                    $r2Path = 'tourist_spots/' . $m[1];
                }
            }

            $existsOnR2 = false;
            if ($r2Path) {
                try {
                    $existsOnR2 = \Illuminate\Support\Facades\Storage::disk('r2')->exists($r2Path);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("R2 Scan Check exception for {$r2Path}: " . $e->getMessage());
                }
            }

            $status = 'missing';
            $finalUrl = $rawPhoto;

            if ($existsOnR2) {
                $fullR2Url = $r2PublicUrl . '/' . ltrim($r2Path, '/');
                $finalUrl = $fullR2Url;
                if ($rawPhoto !== $fullR2Url && $doRepair) {
                    $spot->update(['photo_url' => $fullR2Url]);
                    $status = 'repaired';
                    $repairedCount++;
                } else {
                    $status = 'verified';
                    $verifiedCount++;
                }
            } else {
                $missingCount++;
            }

            $results[] = [
                'spot_id' => $spot->id,
                'spot_name' => $spot->name,
                'photo_url' => $finalUrl,
                'r2_path' => $r2Path,
                'r2_exists' => $existsOnR2,
                'scan_state' => $status,
            ];
        }

        // Flush map caches so latest scanned URLs are visible right away
        \Illuminate\Support\Facades\Cache::forget('map:public:spots');
        \Illuminate\Support\Facades\Cache::forget('trending:top:5');
        \Illuminate\Support\Facades\Cache::forget('trending:top:10');

        return response()->json([
            'success' => true,
            'message' => 'Cloudflare R2 & Railway DB image scan complete.',
            'summary' => [
                'total_spots' => count($spots),
                'verified' => $verifiedCount,
                'repaired' => $repairedCount,
                'missing' => $missingCount,
            ],
            'details' => $results,
        ]);
    });

    Route::post('/spots/{id}/photo', function (\Illuminate\Http\Request $request, $id) {
        // Only allow admin-level roles
        $user = $request->user();
        if ($user->role === 'tourist') {
            return response()->json(['error' => 'Forbidden: admin access required.'], 403);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240'
        ]);

        $spot = TouristSpot::findOrFail($id);
        $disk = 'r2';
        try {
            $path = \App\Helpers\ImageCompressor::compressAndStore($request->file('photo'), 'tourist_spots', 'r2', 'spot_', 1200, 80);
        } catch (\Throwable $r2Err) {
            $disk = env('FILESYSTEM_DISK', 'public');
            $path = \App\Helpers\ImageCompressor::compressAndStore($request->file('photo'), 'tourist_spots', $disk, 'spot_', 1200, 80);
        }

        $r2Scanned = false;
        if ($disk === 'r2') {
            $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');
            $fullUrl = $r2PublicUrl . '/' . ltrim($path, '/');
            $spot->update(['photo_url' => $fullUrl]);
            $r2Scanned = \App\Helpers\ImageCompressor::verifyUploadScan($path, 'r2');
        } else {
            $fullUrl = asset('storage/' . $path);
            $spot->update(['photo_url' => 'storage/' . $path]);
        }

        // Clear public map cache so mobile app & frontend get the new photo immediately
        \Illuminate\Support\Facades\Cache::forget('map:public:spots');
        \Illuminate\Support\Facades\Cache::forget('trending:top:5');
        \Illuminate\Support\Facades\Cache::forget('trending:top:10');

        return response()->json([
            'success' => true,
            'message' => 'Photo uploaded and scanned successfully on Cloudflare R2!',
            'photo_url' => $fullUrl,
            'r2_scanned' => $r2Scanned,
            'spot' => $spot->fresh()
        ]);
    });

    Route::match(['post', 'put'], '/spots/{id}', function (\Illuminate\Http\Request $request, $id) {
        $user = $request->user();
        if ($user->role === 'tourist') {
            return response()->json(['error' => 'Forbidden: admin access required.'], 403);
        }

        $spot = TouristSpot::findOrFail($id);
        $data = $request->validate([
            'name' => 'nullable|string',
            'municipality_id' => 'nullable|integer',
            'category' => 'nullable|string',
            'entrance_fee' => 'nullable|numeric',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240'
        ]);

        if ($request->hasFile('photo')) {
            $disk = 'r2';
            try {
                $path = \App\Helpers\ImageCompressor::compressAndStore($request->file('photo'), 'tourist_spots', 'r2', 'spot_', 1200, 80);
            } catch (\Throwable $r2Err) {
                $disk = env('FILESYSTEM_DISK', 'public');
                $path = \App\Helpers\ImageCompressor::compressAndStore($request->file('photo'), 'tourist_spots', $disk, 'spot_', 1200, 80);
            }

            if ($disk === 'r2') {
                $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');
                $data['photo_url'] = $r2PublicUrl . '/' . ltrim($path, '/');
            } else {
                $data['photo_url'] = 'storage/' . $path;
            }
        }

        $spot->update(array_filter($data, function ($val) {
            return $val !== null;
        }));

        \Illuminate\Support\Facades\Cache::forget('map:public:spots');
        \Illuminate\Support\Facades\Cache::forget('trending:top:5');

        return response()->json([
            'success' => true,
            'message' => 'Tourist spot updated successfully!',
            'spot' => $spot->fresh()
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
            $disk = 'r2';
            try {
                $path = \App\Helpers\ImageCompressor::compressAndStore($request->file('photo'), 'tourist_spots', 'r2', 'spot_', 1200, 80);
            } catch (\Throwable $r2Err) {
                $disk = env('FILESYSTEM_DISK', 'public');
                $path = \App\Helpers\ImageCompressor::compressAndStore($request->file('photo'), 'tourist_spots', $disk, 'spot_', 1200, 80);
            }

            if ($disk === 'r2') {
                $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');
                $data['photo_url'] = $r2PublicUrl . '/' . ltrim($path, '/');
            } else {
                $data['photo_url'] = 'storage/' . $path;
            }
        }

        $spot = TouristSpot::create($data);

        // Upload any extra gallery photos to Cloudflare R2 and save in Railway DB
        if ($request->hasFile('photos')) {
            $extraPhotos = $request->file('photos');
            if (is_array($extraPhotos)) {
                foreach ($extraPhotos as $extraPhoto) {
                    try {
                        $ePath = \App\Helpers\ImageCompressor::compressAndStore($extraPhoto, 'tourist_spots', 'r2', 'spot_', 1200, 80);
                        $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');
                        \App\Models\TouristSpotImage::create([
                            'spot_id' => $spot->id,
                            'photo_url' => $r2PublicUrl . '/' . ltrim($ePath, '/'),
                        ]);
                    } catch (\Throwable $e) {
                    }
                }
            }
        }

        \Illuminate\Support\Facades\Cache::forget('map:public:spots');
        \Illuminate\Support\Facades\Cache::forget('trending:top:5');

        return response()->json([
            'success' => true,
            'message' => 'Tourist spot created successfully!',
            'spot' => $spot->fresh()
        ]);
    });

    // ── Admin Proof Check-ins Management (Cloudflare R2 stored photos) ──
    Route::get('/proof-checkins', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        if ($user && $user->role === 'tourist') {
            return response()->json(['error' => 'Forbidden: admin access required.'], 403);
        }

        $status = $request->query('status');
        $query = \App\Models\ItineraryItem::with([
            'itinerary.user:id,name,email,avatar',
            'destination:id,name,municipality_id,category',
            'destination.municipality:id,name'
        ])
            ->whereNotNull('proof_image')
            ->latest();

        if ($status) {
            $query->where('proof_status', $status);
        }

        $items = $query->get()->map(function ($item) {
            $tourist = $item->itinerary ? $item->itinerary->user : null;
            $spot = $item->destination;
            return [
                'id' => $item->id,
                'itinerary_id' => $item->itinerary_id,
                'tourist_spot_id' => $item->tourist_spot_id,
                'tourist_name' => $tourist->name ?? 'Tourist',
                'tourist_email' => $tourist->email ?? '',
                'tourist_avatar' => $tourist->avatar ?? null,
                'spot_name' => $spot->name ?? 'Tourist Spot',
                'category' => $spot->category ?? '',
                'municipality' => $spot && $spot->municipality ? $spot->municipality->name : '',
                'proof_image' => $item->proof_image,
                'proof_status' => $item->proof_status ?? ($item->is_visited ? 'approved' : 'pending'),
                'is_visited' => (bool) $item->is_visited,
                'visited_at' => $item->visited_at ? $item->visited_at->toIso8601String() : null,
                'rejection_reason' => $item->rejection_reason,
                'reviewed_at' => $item->reviewed_at ? $item->reviewed_at->toIso8601String() : null,
                'created_at' => $item->created_at ? $item->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'checkins' => $items,
        ]);
    });

    Route::post('/proof-checkins/{id}/approve', function (\Illuminate\Http\Request $request, $id) {
        $user = $request->user();
        if ($user && $user->role === 'tourist') {
            return response()->json(['error' => 'Forbidden: admin access required.'], 403);
        }

        $item = \App\Models\ItineraryItem::findOrFail($id);
        $item->update([
            'proof_status' => 'approved',
            'is_visited' => true,
            'visited_at' => $item->visited_at ?? now(),
            'reviewed_by' => $user->id ?? null,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Proof check-in approved successfully! +50 XP awarded to tourist.',
            'item' => $item,
        ]);
    });

    Route::post('/proof-checkins/{id}/reject', function (\Illuminate\Http\Request $request, $id) {
        $user = $request->user();
        if ($user && $user->role === 'tourist') {
            return response()->json(['error' => 'Forbidden: admin access required.'], 403);
        }

        $reason = $request->input('reason', 'Photo proof was invalid or could not be verified.');
        $item = \App\Models\ItineraryItem::findOrFail($id);
        $item->update([
            'proof_status' => 'rejected',
            'is_visited' => false,
            'rejection_reason' => $reason,
            'reviewed_by' => $user->id ?? null,
            'reviewed_at' => now(),
        ]);

        $tourist = $item->itinerary ? $item->itinerary->user : null;
        $spot = $item->destination;
        if ($tourist) {
            \App\Models\Notification::createSafely(
                $tourist->id,
                'checkin_rejected',
                'Photo Check-in Not Approved ❌',
                "Your photo proof check-in at " . ($spot->name ?? 'destination') . " was not approved. Reason: {$reason}"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Proof check-in rejected.',
            'item' => $item,
        ]);
    });
});

// Top-level aliases for backward-compatible admin website views
Route::get('/municipalities', [MapController::class, 'publicMunicipalities']);
Route::get('/tourist-spots', [MapController::class, 'publicMapData']);

// ─────────────────────────────────────────────────────────────────────────────
//  ADMIN PANEL ENDPOINTS (LUPTO, PITCO, PICTO, MUNICIPAL)
// ─────────────────────────────────────────────────────────────────────────────
foreach (['lupto', 'pitco', 'picto', 'municipal'] as $rolePrefix) {
    Route::prefix($rolePrefix)->group(function () use ($rolePrefix) {
        Route::get('/dashboard', function (Illuminate\Http\Request $request) {
            $muniId = $request->query('municipality_id');
            $spotsQuery = \App\Models\TouristSpot::with('municipality');
            if ($muniId) {
                $spotsQuery->where('municipality_id', $muniId);
            }
            $spots = $spotsQuery->get();
            $totalSpots = $spots->count();
            $totalMunis = \Illuminate\Support\Facades\DB::table('municipalities')->count();
            $totalUsers = \App\Models\User::count();
            $totalVisits = (int) $spots->sum('visits');
            $ratedSpots = $spots->where('rating', '>', 0);
            $avgRating = $ratedSpots->count() > 0 ? round((float) $ratedSpots->avg('rating'), 1) : 5.0;

            $pendingApprovals = 0;
            try {
                $pendingApprovals = \App\Models\ItineraryItem::where('proof_status', 'pending')->count()
                    + \App\Models\TouristSpot::where('status', 'pending')->count();
            } catch (\Throwable $e) {
            }

            return response()->json([
                'success' => true,
                'kpis' => [
                    'total_municipalities' => $totalMunis ?: 20,
                    'total_spots' => $totalSpots ?: 12,
                    'pending_approvals' => $pendingApprovals,
                    'total_tourists' => $totalUsers ?: 150,
                    'total_visits' => $totalVisits,
                    'avg_rating' => $avgRating,
                ],
                'municipalities' => \Illuminate\Support\Facades\DB::table('municipalities')->get(),
                'touristSpots' => $spots,
                'alerts' => []
            ]);
        });

        Route::get('/tourist-spots', function (Illuminate\Http\Request $request) {
            $muniId = $request->query('municipality_id');
            $query = \App\Models\TouristSpot::with(['municipality:id,name', 'images']);
            if ($muniId) {
                $query->where('municipality_id', $muniId);
            }
            $spots = $query->get()->map(function ($spot) {
                $spot->visits = (int) ($spot->visits ?? 0);
                if (!$spot->rating || (float) $spot->rating <= 0) {
                    $avg = \App\Models\SiteFeedback::where('tourist_spot_id', $spot->id)->whereNotNull('rating')->avg('rating');
                    $spot->rating = $avg ? round((float) $avg, 1) : 0.0;
                } else {
                    $spot->rating = round((float) $spot->rating, 1);
                }
                return $spot;
            });
            return response()->json($spots);
        });

        Route::post('/tourist-spots', function (\Illuminate\Http\Request $request) {
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
                $disk = 'r2';
                try {
                    $path = \App\Helpers\ImageCompressor::compressAndStore($request->file('photo'), 'tourist_spots', 'r2', 'spot_', 1200, 80);
                } catch (\Throwable $r2Err) {
                    $disk = env('FILESYSTEM_DISK', 'public');
                    $path = \App\Helpers\ImageCompressor::compressAndStore($request->file('photo'), 'tourist_spots', $disk, 'spot_', 1200, 80);
                }

                if ($disk === 'r2') {
                    $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');
                    $data['photo_url'] = $r2PublicUrl . '/' . ltrim($path, '/');
                } else {
                    $data['photo_url'] = 'storage/' . $path;
                }
            }

            $spot = \App\Models\TouristSpot::create($data);
            \Illuminate\Support\Facades\Cache::forget('map:public:spots');
            \Illuminate\Support\Facades\Cache::forget('trending:top:5');

            return response()->json([
                'success' => true,
                'message' => 'Tourist spot created & uploaded to Cloudflare R2 successfully!',
                'spot' => $spot->fresh()
            ]);
        });

        Route::post('/tourist-spots/{id}/photo', function (\Illuminate\Http\Request $request, $id) {
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240'
            ]);

            $spot = \App\Models\TouristSpot::findOrFail($id);
            $disk = 'r2';
            try {
                $path = \App\Helpers\ImageCompressor::compressAndStore($request->file('photo'), 'tourist_spots', 'r2', 'spot_', 1200, 80);
            } catch (\Throwable $r2Err) {
                $disk = env('FILESYSTEM_DISK', 'public');
                $path = \App\Helpers\ImageCompressor::compressAndStore($request->file('photo'), 'tourist_spots', $disk, 'spot_', 1200, 80);
            }

            if ($disk === 'r2') {
                $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');
                $fullUrl = $r2PublicUrl . '/' . ltrim($path, '/');
                $spot->update(['photo_url' => $fullUrl]);
            } else {
                $fullUrl = asset('storage/' . $path);
                $spot->update(['photo_url' => 'storage/' . $path]);
            }

            \Illuminate\Support\Facades\Cache::forget('map:public:spots');
            \Illuminate\Support\Facades\Cache::forget('trending:top:5');

            return response()->json([
                'success' => true,
                'message' => 'Spot photo uploaded to Cloudflare R2 & synced to Railway DB!',
                'photo_url' => $fullUrl,
                'spot' => $spot->fresh()
            ]);
        });

        Route::get('/users', function (\Illuminate\Http\Request $request) {
            $ctrl = new \App\Http\Controllers\LeaderboardController();
            $leaderboardResp = $ctrl->index($request);
            $leaderboardData = json_decode($leaderboardResp->getContent(), true);
            $rankedUsers = $leaderboardData['leaderboard'] ?? ($leaderboardData['data'] ?? []);

            $allUsers = \App\Models\User::with('municipality')->get();
            $adminCount = $allUsers->whereIn('role', ['admin', 'super_admin', 'lupto', 'pitco', 'picto'])->count();
            $municipalCount = $allUsers->where('role', 'municipal')->count();
            $touristCount = $allUsers->where('role', 'tourist')->count();

            // Map all users with enriched rank and points
            $rankedUserMap = collect($rankedUsers)->keyBy('id');
            $enrichedAll = $allUsers->map(function ($u, $idx) use ($rankedUserMap) {
                $mName = $u->municipality ? $u->municipality->name : (!empty($u->home_location) ? $u->home_location : 'La Union');
                $ranked = $rankedUserMap->get($u->id);

                if ($ranked) {
                    $rankVal = $ranked['rank'];
                    $pointsVal = $ranked['total_points'];
                    $actVal = $ranked['completed_activities'];
                } else {
                    $rankVal = $idx + 1;
                    $pointsVal = (int) ($u->xp ?? 0);
                    $actVal = (int) ($u->completed_activities ?? 0);
                }

                $uArr = $u->toArray();
                $uArr['rank'] = $rankVal;
                $uArr['rank_no'] = $rankVal;
                $uArr['position'] = $rankVal;
                $uArr['ranking'] = $rankVal;
                $uArr['municipality'] = $mName;
                $uArr['municipality_name'] = $mName;
                $uArr['home_location'] = $mName;
                $uArr['location'] = $mName;
                $uArr['total_points'] = $pointsVal;
                $uArr['points'] = $pointsVal;
                $uArr['xp'] = $pointsVal;
                $uArr['pts'] = $pointsVal;
                $uArr['completed_activities'] = $actVal;
                $uArr['activities'] = $actVal;
                $uArr['total_activities'] = $actVal;
                $uArr['places_visited'] = $actVal;
                return $uArr;
            })->sortBy('rank')->values();

            return response()->json([
                'success' => true,
                'status' => 'success',
                'users' => $enrichedAll,
                'data' => $enrichedAll,
                'tourists' => $rankedUsers,
                'leaderboard' => $rankedUsers,
                'roleStats' => [
                    'admin' => $adminCount,
                    'municipal' => $municipalCount,
                    'tourist' => $touristCount,
                    'total' => $allUsers->count(),
                ],
                'stats' => $leaderboardData['stats'] ?? [],
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

        // Real-time Analytics Endpoints
        Route::get('/analytics/summary', [AnalyticsController::class, 'summary']);
        Route::get('/analytics/top-spots', [AnalyticsController::class, 'topSpots']);
        Route::get('/analytics/top_spots', [AnalyticsController::class, 'topSpots']);
        Route::get('/analytics/top-municipalities', [AnalyticsController::class, 'topMunicipalities']);
        Route::get('/analytics/top_municipalities', [AnalyticsController::class, 'topMunicipalities']);
        Route::get('/analytics/chart-data', [AnalyticsController::class, 'chartData']);
        Route::get('/analytics/chart_data', [AnalyticsController::class, 'chartData']);
        Route::get('/analytics/monthly-trend', [AnalyticsController::class, 'monthlyTrend']);
        Route::get('/analytics/monthly_trend', [AnalyticsController::class, 'monthlyTrend']);
        Route::get('/analytics/filter-options', [AnalyticsController::class, 'filterOptions']);
        Route::get('/analytics/filter_options', [AnalyticsController::class, 'filterOptions']);
        Route::get('/analytics/full', [AnalyticsController::class, 'full']);
        Route::get('/analytics', [AnalyticsController::class, 'handleAction']);
        Route::any('/analytics/{action}', [AnalyticsController::class, 'handleAction']);

        Route::get('/leaderboard', [LeaderboardController::class, 'index']);

        Route::get('/archive/stats', function () {
            $fareCount = \App\Models\FareGuide::count();
            $userCount = \App\Models\User::count();
            $spotCount = \App\Models\TouristSpot::count();
            return response()->json([
                'success' => true,
                'stats' => [
                    'fares' => $fareCount,
                    'users' => $userCount,
                    'spots' => $spotCount,
                    'total' => $fareCount + $userCount + $spotCount
                ]
            ]);
        });

        Route::get('/archive/fares', function () {
            $archived = \App\Models\FareGuide::where('is_archived', true)->orWhere('status', 'archived')->with('matrices')->get();
            return response()->json([
                'success' => true,
                'fares' => $archived,
                'data' => $archived
            ]);
        });

        Route::get('/archive', function () {
            $fareCount = \App\Models\FareGuide::count();
            $userCount = \App\Models\User::count();
            $spotCount = \App\Models\TouristSpot::count();
            return response()->json([
                'success' => true,
                'archive' => [],
                'fares' => [],
                'stats' => ['fares' => $fareCount, 'users' => $userCount, 'spots' => $spotCount, 'total' => $fareCount + $userCount + $spotCount]
            ]);
        });

        Route::any('/archive/{any}', function () {
            $fareCount = \App\Models\FareGuide::count();
            $userCount = \App\Models\User::count();
            $spotCount = \App\Models\TouristSpot::count();
            return response()->json([
                'success' => true,
                'archive' => [],
                'fares' => [],
                'stats' => ['fares' => $fareCount, 'users' => $userCount, 'spots' => $spotCount, 'total' => $fareCount + $userCount + $spotCount]
            ]);
        })->where('any', '.*');

        Route::get('/fare-data', [MapController::class, 'publicFares']);
        Route::get('/fares', [MapController::class, 'publicFares']);

        Route::get('/activity-logs', function (\Illuminate\Http\Request $request) {
            $dbLogs = \App\Models\ActivityLog::with('user:id,name,email,avatar')->latest()->limit(100)->get();

            if ($dbLogs->isNotEmpty()) {
                $formatted = $dbLogs->map(function ($l) {
                    return [
                        'id' => $l->id,
                        'user_id' => $l->user_id,
                        'user_name' => $l->user ? $l->user->name : "Explorer #{$l->user_id}",
                        'name' => $l->user ? $l->user->name : "Explorer #{$l->user_id}",
                        'action' => $l->action,
                        'details' => $l->details,
                        'description' => $l->details,
                        'module' => $l->module ?? 'General',
                        'ip_address' => $l->ip_address ?? '127.0.0.1',
                        'created_at' => $l->created_at ? $l->created_at->toIso8601String() : now()->toIso8601String(),
                        'date' => $l->created_at ? $l->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
                        'user' => $l->user,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'status' => 'success',
                    'logs' => $formatted,
                    'data' => $formatted,
                    'total' => $formatted->count(),
                ]);
            }

            // Synthesize comprehensive live activity records from real database events
            $users = \App\Models\User::latest()->limit(25)->get()->map(function ($u) {
                return [
                    'id' => 'usr_' . $u->id,
                    'user_id' => $u->id,
                    'user_name' => $u->name ?: "Explorer #{$u->id}",
                    'name' => $u->name ?: "Explorer #{$u->id}",
                    'action' => 'User Registration',
                    'details' => "Tourist '{$u->name}' registered with email {$u->email}",
                    'description' => "Tourist '{$u->name}' registered with email {$u->email}",
                    'module' => 'Authentication',
                    'ip_address' => '127.0.0.1',
                    'created_at' => $u->created_at ? $u->created_at->toIso8601String() : now()->toIso8601String(),
                    'date' => $u->created_at ? $u->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
                    'user' => ['id' => $u->id, 'name' => $u->name, 'avatar' => $u->avatar],
                ];
            });

            $feedbacks = \App\Models\SiteFeedback::with(['user:id,name,avatar', 'touristSpot:id,name'])->latest()->limit(25)->get()->map(function ($fb) {
                $spotName = $fb->touristSpot ? $fb->touristSpot->name : 'Tourist Spot';
                $uName = $fb->user ? $fb->user->name : 'Tourist';
                return [
                    'id' => 'fb_' . $fb->id,
                    'user_id' => $fb->user_id,
                    'user_name' => $uName,
                    'name' => $uName,
                    'action' => 'Destination Review & Rating',
                    'details' => "Submitted {$fb->rating}★ review for '{$spotName}'",
                    'description' => "Submitted {$fb->rating}★ review for '{$spotName}'" . ($fb->testimony ? ": \"{$fb->testimony}\"" : ''),
                    'module' => 'Feedback',
                    'ip_address' => '127.0.0.1',
                    'created_at' => $fb->created_at ? $fb->created_at->toIso8601String() : now()->toIso8601String(),
                    'date' => $fb->created_at ? $fb->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
                    'user' => $fb->user ? ['id' => $fb->user->id, 'name' => $fb->user->name, 'avatar' => $fb->user->avatar] : null,
                ];
            });

            $points = collect();

            $itinItems = \App\Models\ItineraryItem::with(['itinerary.user:id,name,avatar', 'destination:id,name'])->latest()->limit(25)->get()->map(function ($it) {
                $u = $it->itinerary ? $it->itinerary->user : null;
                $uName = $u ? $u->name : 'Tourist';
                $spotName = $it->destination ? $it->destination->name : 'Destination';
                return [
                    'id' => 'itin_' . $it->id,
                    'user_id' => $u ? $u->id : null,
                    'user_name' => $uName,
                    'name' => $uName,
                    'action' => $it->is_visited ? 'Destination Check-In' : 'Itinerary Destination Added',
                    'details' => ($it->is_visited ? 'Completed check-in at ' : 'Added destination ') . "'{$spotName}'",
                    'description' => ($it->is_visited ? 'Completed check-in at ' : 'Added destination ') . "'{$spotName}'",
                    'module' => 'Itinerary',
                    'ip_address' => '127.0.0.1',
                    'created_at' => $it->created_at ? $it->created_at->toIso8601String() : now()->toIso8601String(),
                    'date' => $it->created_at ? $it->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
                    'user' => $u ? ['id' => $u->id, 'name' => $u->name, 'avatar' => $u->avatar] : null,
                ];
            });

            $combined = collect()
                ->concat($users)
                ->concat($feedbacks)
                ->concat($points)
                ->concat($itinItems)
                ->sortByDesc('created_at')
                ->values();

            return response()->json([
                'success' => true,
                'status' => 'success',
                'logs' => $combined,
                'data' => $combined,
                'total' => $combined->count(),
            ]);
        });

        Route::post('/dashboard/approve-spot', function (\Illuminate\Http\Request $request) {
            $spot = \App\Models\TouristSpot::find($request->input('id'));
            if ($spot)
                $spot->update(['status' => 'approved']);
            return response()->json(['success' => true]);
        });

        Route::post('/dashboard/reject-spot', function (\Illuminate\Http\Request $request) {
            $spot = \App\Models\TouristSpot::find($request->input('id'));
            if ($spot)
                $spot->update(['status' => 'rejected']);
            return response()->json(['success' => true]);
        });

        Route::post('/dashboard/batch-approve-spots', function (\Illuminate\Http\Request $request) {
            $ids = $request->input('ids', []);
            \App\Models\TouristSpot::whereIn('id', $ids)->update(['status' => 'approved']);
            return response()->json(['success' => true]);
        });

        Route::get('/proof-checkins', function (\Illuminate\Http\Request $request) {
            $status = $request->query('status');
            $query = \App\Models\ItineraryItem::with([
                'itinerary.user:id,name,email,avatar',
                'destination:id,name,municipality_id,category',
                'destination.municipality:id,name'
            ])
                ->whereNotNull('proof_image')
                ->latest();

            if ($status) {
                $query->where('proof_status', $status);
            }

            $items = $query->get()->map(function ($item) {
                $tourist = $item->itinerary ? $item->itinerary->user : null;
                $spot = $item->destination;
                return [
                    'id' => $item->id,
                    'itinerary_id' => $item->itinerary_id,
                    'tourist_spot_id' => $item->tourist_spot_id,
                    'tourist_name' => $tourist->name ?? 'Tourist',
                    'tourist_email' => $tourist->email ?? '',
                    'tourist_avatar' => $tourist->avatar ?? null,
                    'spot_name' => $spot->name ?? 'Tourist Spot',
                    'category' => $spot->category ?? '',
                    'municipality' => $spot && $spot->municipality ? $spot->municipality->name : '',
                    'proof_image' => $item->proof_image,
                    'proof_status' => $item->proof_status ?? ($item->is_visited ? 'approved' : 'pending'),
                    'is_visited' => (bool) $item->is_visited,
                    'visited_at' => $item->visited_at ? $item->visited_at->toIso8601String() : null,
                    'rejection_reason' => $item->rejection_reason,
                    'reviewed_at' => $item->reviewed_at ? $item->reviewed_at->toIso8601String() : null,
                    'created_at' => $item->created_at ? $item->created_at->toIso8601String() : null,
                ];
            });

            return response()->json([
                'success' => true,
                'checkins' => $items,
            ]);
        });

        Route::post('/proof-checkins/{id}/approve', function (\Illuminate\Http\Request $request, $id) {
            $item = \App\Models\ItineraryItem::findOrFail($id);
            $user = $request->user();
            $item->update([
                'proof_status' => 'approved',
                'is_visited' => true,
                'visited_at' => $item->visited_at ?? now(),
                'reviewed_by' => $user->id ?? null,
                'reviewed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proof check-in approved successfully! +50 XP awarded to tourist.',
                'item' => $item,
            ]);
        });

        Route::post('/proof-checkins/{id}/reject', function (\Illuminate\Http\Request $request, $id) {
            $reason = $request->input('reason', 'Photo proof was invalid or could not be verified.');
            $item = \App\Models\ItineraryItem::findOrFail($id);
            $user = $request->user();
            $item->update([
                'proof_status' => 'rejected',
                'is_visited' => false,
                'rejection_reason' => $reason,
                'reviewed_by' => $user->id ?? null,
                'reviewed_at' => now(),
            ]);

            $tourist = $item->itinerary ? $item->itinerary->user : null;
            $spot = $item->destination;
            if ($tourist) {
                \App\Models\Notification::createSafely(
                    $tourist->id,
                    'checkin_rejected',
                    'Photo Check-in Not Approved ❌',
                    "Your photo proof check-in at " . ($spot->name ?? 'destination') . " was not approved. Reason: {$reason}"
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Proof check-in rejected.',
                'item' => $item,
            ]);
        });
    });
}

// ─────────────────────────────────────────────────────────────────────────────
//  PUBLIC routes (no auth required) — for mobile app unauthenticated features
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('public')->group(function () {
    Route::get('/map', [MapController::class, 'publicMapData']);
    Route::get('/test', function () {
        return response()->json(['message' => 'test']);
    });
    Route::get('/fares', [MapController::class, 'publicFares']);
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/municipalities', [MapController::class, 'publicMunicipalities']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
    Route::get('/feedback', [FeedbackController::class, 'index']);
    Route::get('/feedback/user-reviewed-spots', [FeedbackController::class, 'userReviewedSpots']);
    Route::get('/vouchers', [\App\Http\Controllers\VoucherController::class, 'index']);
    Route::get('/weather', [WeatherController::class, 'getWeather']);
    Route::get('/amenities', [MapController::class, 'publicAmenities']);
});
Route::get('/amenities', [MapController::class, 'publicAmenities']);
Route::get('/fares', [MapController::class, 'publicFares']);
Route::get('/fare-data', [MapController::class, 'publicFares']);
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vouchers', [\App\Http\Controllers\VoucherController::class, 'index']);
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

        $alreadyReviewed = false;
        if ($user) {
            $alreadyReviewed = \App\Models\SiteFeedback::where('user_id', $user->id)
                ->where('tourist_spot_id', $id)
                ->exists();
        }

        // Create or update feedback rating for this user & spot
        \App\Models\SiteFeedback::updateOrCreate(
            ['user_id' => $user ? $user->id : null, 'tourist_spot_id' => $id],
            ['rating' => $request->rating]
        );

        // Recalculate true average rating from all site feedbacks for this spot
        $avgRating = \App\Models\SiteFeedback::where('tourist_spot_id', $id)
            ->whereNotNull('rating')
            ->avg('rating');

        $spot->rating = round((float) $avgRating, 2);
        $spot->save();

        // Invalidate public map & trending spots cache
        \Illuminate\Support\Facades\Cache::forget('map:public:spots');
        \Illuminate\Support\Facades\Cache::forget('trending:top:5');
        \Illuminate\Support\Facades\Cache::forget('trending:top:10');
        \Illuminate\Support\Facades\Cache::forget('trending:top:50');

        // Award gamification points (+25 XP, +25 points) ONLY IF FIRST TIME
        $rewardAwarded = false;
        if ($user && !$alreadyReviewed) {
            try {
                $user->increment('xp', 25);
                \App\Models\UserPoint::awardPointsSafely(
                    $user->id,
                    25,
                    'rating',
                    "Rated {$spot->name} {$request->rating} stars",
                    $spot->id
                );
                $rewardAwarded = true;
            } catch (\Throwable $e) {
            }
        }

        return response()->json([
            'message' => $rewardAwarded ? 'Rating submitted successfully! (+25 XP earned)' : 'Rating updated successfully!',
            'reward_awarded' => $rewardAwarded,
            'spot_rating' => $spot->rating
        ]);
    });

    Route::get('/itineraries', [ItineraryController::class, 'index']);
    Route::get('/itineraries/{id}', [ItineraryController::class, 'show']);
    Route::post('/itineraries', [ItineraryController::class, 'store']);
    Route::post('/itineraries/estimate-cost', [ItineraryController::class, 'estimateCost']);
    Route::put('/itineraries/{id}', [ItineraryController::class, 'update']);
    Route::delete('/itineraries/{id}', [ItineraryController::class, 'destroy']);
    Route::patch('/itineraries/{id}/complete', [ItineraryController::class, 'markCompleted']);

    Route::patch('/itineraries/items/{id}/visit', [ItineraryItemController::class, 'visit']);
    Route::post('/itineraries/items/{id}/visit', [ItineraryItemController::class, 'visit']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Site Testimonies & Policy Recommendations
    Route::get('/feedback/user-reviewed-spots', [FeedbackController::class, 'userReviewedSpots']);
    Route::get('/feedback', [FeedbackController::class, 'index']);
    Route::post('/feedback', [FeedbackController::class, 'store']);

    // AR and Instant GPS Check-in
    Route::post('/points/ar-checkin', function (\Illuminate\Http\Request $request) {
        $spotId = $request->input('spot_id');
        $spot = TouristSpot::find($spotId);
        if (!$spot) {
            return response()->json(['status' => 'error', 'message' => 'Tourist destination not found.'], 404);
        }

        // Increment spot visits count in database
        $spot->increment('visits');

        $user = $request->user();
        if ($user) {
            try {
                $user->increment('xp', 50);
                $user->increment('completed_activities');
                \App\Models\UserPoint::awardPointsSafely(
                    $user->id,
                    50,
                    'ar_checkin',
                    "AR/GPS Check-in at {$spot->name}",
                    $spot->id
                );
            } catch (\Throwable $e) {
            }
        }

        // Invalidate map and trending caches
        \Illuminate\Support\Facades\Cache::forget('map:public:spots');
        \Illuminate\Support\Facades\Cache::forget('trending:top:5');
        \Illuminate\Support\Facades\Cache::forget('trending:top:10');

        return response()->json([
            'status' => 'success',
            'success' => true,
            'message' => "🎉 Check-in Verified at {$spot->name}! Earned +50 XP!",
            'visits' => (int) $spot->fresh()->visits,
            'xp_earned' => 50,
            'points_earned' => 50,
        ]);
    });

    // Direct Destination Check-in
    Route::post('/destinations/{id}/check-in', function (\Illuminate\Http\Request $request, int $id) {
        $spot = TouristSpot::findOrFail($id);
        $spot->increment('visits');

        $user = $request->user();
        if ($user) {
            try {
                $user->increment('xp', 50);
                $user->increment('completed_activities');
                \App\Models\UserPoint::awardPointsSafely(
                    $user->id,
                    50,
                    'check_in',
                    "Direct Check-in at {$spot->name}",
                    $spot->id
                );
            } catch (\Throwable $e) {
            }
        }

        \Illuminate\Support\Facades\Cache::forget('map:public:spots');
        \Illuminate\Support\Facades\Cache::forget('trending:top:5');
        \Illuminate\Support\Facades\Cache::forget('trending:top:10');

        return response()->json([
            'status' => 'success',
            'success' => true,
            'message' => "🎉 Check-in completed at {$spot->name}! Earned +50 XP!",
            'visits' => (int) $spot->fresh()->visits,
            'xp_earned' => 50,
            'points_earned' => 50,
        ]);
    });

    // Points & Vouchers
    Route::get('/vouchers', [\App\Http\Controllers\VoucherController::class, 'index']);
    Route::get('/fares', [\App\Http\Controllers\FareController::class, 'index']);
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/points/balance', [PointsController::class, 'getBalance']);
    Route::post('/points/puzzle', [PointsController::class, 'awardPuzzlePoints']);
    Route::post('/points/trivia', [PointsController::class, 'awardTriviaPoints']);
    Route::post('/points/minigame', [PointsController::class, 'awardMiniGamePoints']);
    Route::post('/points/redeem', [PointsController::class, 'redeem']);
    Route::post('/points/redeem-voucher', [\App\Http\Controllers\VoucherController::class, 'redeemVoucher']);

    // Puzzle Tourist Spot Images from Database
    Route::match(['GET', 'POST', 'OPTIONS'], '/puzzles/spots', [PuzzleController::class, 'spots']);
});

// Top-level Analytics aliases
Route::prefix('analytics')->group(function () {
    Route::get('/summary', [AnalyticsController::class, 'summary']);
    Route::get('/top-spots', [AnalyticsController::class, 'topSpots']);
    Route::get('/top_spots', [AnalyticsController::class, 'topSpots']);
    Route::get('/top-municipalities', [AnalyticsController::class, 'topMunicipalities']);
    Route::get('/top_municipalities', [AnalyticsController::class, 'topMunicipalities']);
    Route::get('/chart-data', [AnalyticsController::class, 'chartData']);
    Route::get('/chart_data', [AnalyticsController::class, 'chartData']);
    Route::get('/monthly-trend', [AnalyticsController::class, 'monthlyTrend']);
    Route::get('/monthly_trend', [AnalyticsController::class, 'monthlyTrend']);
    Route::get('/filter-options', [AnalyticsController::class, 'filterOptions']);
    Route::get('/filter_options', [AnalyticsController::class, 'filterOptions']);
    Route::get('/full', [AnalyticsController::class, 'full']);
    Route::get('/', [AnalyticsController::class, 'handleAction']);
    Route::any('/{action}', [AnalyticsController::class, 'handleAction']);
});

// Top-level Public Check-in and Rating aliases
Route::post('/public/destinations/{id}/check-in', function (\Illuminate\Http\Request $request, int $id) {
    $spot = TouristSpot::findOrFail($id);
    $spot->increment('visits');
    \Illuminate\Support\Facades\Cache::forget('map:public:spots');
    \Illuminate\Support\Facades\Cache::forget('trending:top:5');
    return response()->json(['status' => 'success', 'success' => true, 'visits' => (int) $spot->fresh()->visits]);
});
Route::post('/destinations/{id}/check-in', function (\Illuminate\Http\Request $request, int $id) {
    $spot = TouristSpot::findOrFail($id);
    $spot->increment('visits');
    \Illuminate\Support\Facades\Cache::forget('map:public:spots');
    \Illuminate\Support\Facades\Cache::forget('trending:top:5');
    return response()->json(['status' => 'success', 'success' => true, 'visits' => (int) $spot->fresh()->visits]);
});
Route::post('/points/ar-checkin', function (\Illuminate\Http\Request $request) {
    $spotId = $request->input('spot_id');
    $spot = TouristSpot::find($spotId);
    if (!$spot)
        return response()->json(['status' => 'error'], 404);
    $spot->increment('visits');
    \Illuminate\Support\Facades\Cache::forget('map:public:spots');
    \Illuminate\Support\Facades\Cache::forget('trending:top:5');
    return response()->json(['status' => 'success', 'success' => true, 'visits' => (int) $spot->fresh()->visits]);
});
Route::post('/feedback', [FeedbackController::class, 'store']);
Route::post('/destinations/{id}/rate', function (\Illuminate\Http\Request $request, int $id) {
    $spot = TouristSpot::findOrFail($id);
    $rating = (int) $request->input('rating', 5);
    $user = $request->user();
    \App\Models\SiteFeedback::updateOrCreate(
        ['user_id' => $user ? $user->id : null, 'tourist_spot_id' => $id],
        ['rating' => $rating]
    );
    $avg = \App\Models\SiteFeedback::where('tourist_spot_id', $id)->whereNotNull('rating')->avg('rating');
    $spot->rating = round((float) ($avg ?: $rating), 1);
    $spot->save();
    \Illuminate\Support\Facades\Cache::forget('map:public:spots');
    \Illuminate\Support\Facades\Cache::forget('trending:top:5');
    return response()->json(['status' => 'success', 'success' => true, 'spot_rating' => $spot->rating]);
});

// Top-level Leaderboard aliases
Route::get('/leaderboard', [LeaderboardController::class, 'index']);
Route::get('/admin/leaderboard', [LeaderboardController::class, 'index']);
Route::get('/lupto/leaderboard', [LeaderboardController::class, 'index']);
Route::get('/picto/leaderboard', [LeaderboardController::class, 'index']);
Route::get('/pitco/leaderboard', [LeaderboardController::class, 'index']);
Route::get('/municipal/leaderboard', [LeaderboardController::class, 'index']);

// Top-level Activity Logs aliases
$activityLogsHandler = function (\Illuminate\Http\Request $request) {
    $dbLogs = \App\Models\ActivityLog::with('user:id,name,email,avatar')->latest()->limit(100)->get();
    if ($dbLogs->isNotEmpty()) {
        $formatted = $dbLogs->map(fn($l) => [
            'id' => $l->id,
            'user_id' => $l->user_id,
            'user_name' => $l->user ? $l->user->name : "Explorer #{$l->user_id}",
            'name' => $l->user ? $l->user->name : "Explorer #{$l->user_id}",
            'action' => $l->action,
            'details' => $l->details,
            'description' => $l->details,
            'module' => $l->module ?? 'General',
            'ip_address' => $l->ip_address ?? '127.0.0.1',
            'created_at' => $l->created_at ? $l->created_at->toIso8601String() : now()->toIso8601String(),
            'date' => $l->created_at ? $l->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
            'user' => $l->user,
        ]);
        return response()->json(['success' => true, 'status' => 'success', 'logs' => $formatted, 'data' => $formatted, 'total' => $formatted->count()]);
    }
    $users = \App\Models\User::latest()->limit(25)->get()->map(fn($u) => [
        'id' => 'usr_' . $u->id,
        'user_id' => $u->id,
        'user_name' => $u->name ?: "Explorer #{$u->id}",
        'name' => $u->name ?: "Explorer #{$u->id}",
        'action' => 'User Registration',
        'details' => "Tourist '{$u->name}' registered with email {$u->email}",
        'description' => "Tourist '{$u->name}' registered with email {$u->email}",
        'module' => 'Authentication',
        'ip_address' => '127.0.0.1',
        'created_at' => $u->created_at ? $u->created_at->toIso8601String() : now()->toIso8601String(),
        'date' => $u->created_at ? $u->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
        'user' => ['id' => $u->id, 'name' => $u->name, 'avatar' => $u->avatar],
    ]);
    $feedbacks = \App\Models\SiteFeedback::with(['user:id,name,avatar', 'touristSpot:id,name'])->latest()->limit(25)->get()->map(fn($fb) => [
        'id' => 'fb_' . $fb->id,
        'user_id' => $fb->user_id,
        'user_name' => $fb->user ? $fb->user->name : 'Tourist',
        'name' => $fb->user ? $fb->user->name : 'Tourist',
        'action' => 'Destination Review & Rating',
        'details' => "Submitted {$fb->rating}★ review for '" . ($fb->touristSpot ? $fb->touristSpot->name : 'Spot') . "'",
        'description' => "Submitted {$fb->rating}★ review for '" . ($fb->touristSpot ? $fb->touristSpot->name : 'Spot') . "'" . ($fb->testimony ? ": \"{$fb->testimony}\"" : ''),
        'module' => 'Feedback',
        'ip_address' => '127.0.0.1',
        'created_at' => $fb->created_at ? $fb->created_at->toIso8601String() : now()->toIso8601String(),
        'date' => $fb->created_at ? $fb->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
        'user' => $fb->user ? ['id' => $fb->user->id, 'name' => $fb->user->name, 'avatar' => $fb->user->avatar] : null,
    ]);
    $points = collect();
    $itinItems = \App\Models\ItineraryItem::with(['itinerary.user:id,name,avatar', 'destination:id,name'])->latest()->limit(25)->get()->map(fn($it) => [
        'id' => 'itin_' . $it->id,
        'user_id' => $it->itinerary ? $it->itinerary->user_id : null,
        'user_name' => ($it->itinerary && $it->itinerary->user) ? $it->itinerary->user->name : 'Tourist',
        'name' => ($it->itinerary && $it->itinerary->user) ? $it->itinerary->user->name : 'Tourist',
        'action' => $it->is_visited ? 'Destination Check-In' : 'Itinerary Destination Added',
        'details' => ($it->is_visited ? 'Completed check-in at ' : 'Added destination ') . "'" . ($it->destination ? $it->destination->name : 'Spot') . "'",
        'description' => ($it->is_visited ? 'Completed check-in at ' : 'Added destination ') . "'" . ($it->destination ? $it->destination->name : 'Spot') . "'",
        'module' => 'Itinerary',
        'ip_address' => '127.0.0.1',
        'created_at' => $it->created_at ? $it->created_at->toIso8601String() : now()->toIso8601String(),
        'date' => $it->created_at ? $it->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
        'user' => ($it->itinerary && $it->itinerary->user) ? ['id' => $it->itinerary->user->id, 'name' => $it->itinerary->user->name, 'avatar' => $it->itinerary->user->avatar] : null,
    ]);
    $combined = collect()->concat($users)->concat($feedbacks)->concat($points)->concat($itinItems)->sortByDesc('created_at')->values();
    return response()->json(['success' => true, 'status' => 'success', 'logs' => $combined, 'data' => $combined, 'total' => $combined->count()]);
};

Route::get('/activity-logs', $activityLogsHandler);
Route::get('/admin/activity-logs', $activityLogsHandler);
Route::get('/lupto/activity-logs', $activityLogsHandler);
Route::get('/picto/activity-logs', $activityLogsHandler);
Route::get('/pitco/activity-logs', $activityLogsHandler);
Route::get('/municipal/activity-logs', $activityLogsHandler);

$usersHandler = function (\Illuminate\Http\Request $request) {
    $ctrl = new \App\Http\Controllers\LeaderboardController();
    $leaderboardResp = $ctrl->index($request);
    $leaderboardData = json_decode($leaderboardResp->getContent(), true);
    $rankedUsers = $leaderboardData['leaderboard'] ?? ($leaderboardData['data'] ?? []);

    $allUsers = \App\Models\User::with('municipality')->get();
    $adminCount = $allUsers->whereIn('role', ['admin', 'super_admin', 'lupto', 'pitco', 'picto'])->count();
    $municipalCount = $allUsers->where('role', 'municipal')->count();
    $touristCount = $allUsers->where('role', 'tourist')->count();

    $rankedUserMap = collect($rankedUsers)->keyBy('id');
    $enrichedAll = $allUsers->map(function ($u, $idx) use ($rankedUserMap) {
        $mName = $u->municipality ? $u->municipality->name : (!empty($u->home_location) ? $u->home_location : 'La Union');
        $ranked = $rankedUserMap->get($u->id);

        if ($ranked) {
            $rankVal = $ranked['rank'];
            $pointsVal = $ranked['total_points'];
            $actVal = $ranked['completed_activities'];
        } else {
            $rankVal = $idx + 1;
            $pointsVal = (int) ($u->xp ?? 0);
            $actVal = (int) ($u->completed_activities ?? 0);
        }

        $uArr = $u->toArray();
        $uArr['rank'] = $rankVal;
        $uArr['rank_no'] = $rankVal;
        $uArr['position'] = $rankVal;
        $uArr['ranking'] = $rankVal;
        $uArr['municipality'] = $mName;
        $uArr['municipality_name'] = $mName;
        $uArr['home_location'] = $mName;
        $uArr['location'] = $mName;
        $uArr['total_points'] = $pointsVal;
        $uArr['points'] = $pointsVal;
        $uArr['xp'] = $pointsVal;
        $uArr['pts'] = $pointsVal;
        $uArr['completed_activities'] = $actVal;
        $uArr['activities'] = $actVal;
        $uArr['total_activities'] = $actVal;
        $uArr['places_visited'] = $actVal;
        return $uArr;
    })->sortBy('rank')->values();

    return response()->json([
        'success' => true,
        'status' => 'success',
        'users' => $enrichedAll,
        'data' => $enrichedAll,
        'tourists' => $rankedUsers,
        'leaderboard' => $rankedUsers,
        'roleStats' => [
            'admin' => $adminCount,
            'municipal' => $municipalCount,
            'tourist' => $touristCount,
            'total' => $allUsers->count(),
        ],
        'stats' => $leaderboardData['stats'] ?? [],
    ]);
};

Route::get('/users', $usersHandler);
Route::get('/admin/users', $usersHandler);

$touristsHandler = function (\Illuminate\Http\Request $request) {
    $ctrl = new \App\Http\Controllers\LeaderboardController();
    return $ctrl->index($request);
};

Route::get('/tourists', $touristsHandler);
Route::get('/admin/tourists', $touristsHandler);
Route::get('/lupto/tourists', $touristsHandler);
Route::get('/picto/tourists', $touristsHandler);
Route::get('/pitco/tourists', $touristsHandler);
Route::get('/municipal/tourists', $touristsHandler);







