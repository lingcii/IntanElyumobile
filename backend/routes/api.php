<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FareDataController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\Pitco\ArchiveManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportGeneratorController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TouristSpotController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Tourist\DashboardController as TouristDashboardController;
use App\Http\Controllers\Tourist\ProfileController as TouristProfileController;
use App\Http\Controllers\Tourist\FavoriteController;
use App\Http\Controllers\Tourist\ItineraryController;
use App\Http\Controllers\Tourist\ItineraryItemController;
use App\Http\Controllers\MerchandiseController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
//  Auth (public)
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login',    [LoginController::class,   'login']);
    Route::post('/logout',   [LogoutController::class,  'logout']);
    Route::post('/register', [RegisterController::class,'register']);
    Route::get('/check',     [SessionController::class, 'check']);
    
    // Dummy Google Auth redirect for UI testing
    Route::get('/google/redirect', function () {
        return response()->json([
            'success' => false,
            'message' => 'Google Authentication is not yet configured on the backend. This requires a Google Cloud Console Client ID and Secret.'
        ], 501);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
//  Authenticated routes
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('auth.session')->group(function () {

    // Profile (any authenticated role)
    Route::prefix('profile')->group(function () {
        Route::get('/',          [ProfileController::class, 'show']);
        Route::put('/',          [ProfileController::class, 'update']);
        Route::put('/password',  [ProfileController::class, 'updatePassword']);
    });

    // ─────────────────────────────────────────────────────────────────────────
    //  SHARED TOURIST SPOTS (all roles - PICTO: read-only; LUPTO/MUNICIPAL: full CRUD)
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('tourist-spots')->group(function () {
        Route::get('/',               [TouristSpotController::class, 'index']);
        Route::get('/{id}',           [TouristSpotController::class, 'show']);
        Route::post('/upload-image',  [TouristSpotController::class, 'uploadImage']);
        Route::post('/',              [TouristSpotController::class, 'store']);
        Route::put('/{id}',           [TouristSpotController::class, 'update']);
        Route::delete('/{id}',        [TouristSpotController::class, 'destroy']);
    });

    // Municipalities (shared read)
    Route::get('/municipalities',      [MunicipalityController::class, 'index']);
    Route::get('/municipalities/{id}', [MunicipalityController::class, 'show']);

    // ─────────────────────────────────────────────────────────────────────────
    //  PITCO (picto role)
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('pitco')->middleware('role:picto')->group(function () {
        // Dashboard
        Route::get('/dashboard',                    [DashboardController::class, 'index']);
        Route::get('/dashboard/pending-spots',      [DashboardController::class, 'pendingSpots']);
        Route::post('/dashboard/approve-spot',      [DashboardController::class, 'approveSpot']);
        Route::post('/dashboard/reject-spot',       [DashboardController::class, 'rejectSpot']);
        Route::post('/dashboard/batch-approve-spots',[DashboardController::class,'batchApproveSpots']);
        Route::get('/map',                          [MapController::class, 'luptoMapData']);

        // Tourist Spots (read-only, alias to shared controller)
        Route::get('/tourist-spots',     [TouristSpotController::class, 'index']);
        Route::get('/tourist-spots/{id}',[TouristSpotController::class, 'show']);

        // Analytics
            Route::prefix('analytics')->group(function () {
                Route::get('/summary',              [AnalyticsController::class, 'summary']);
                Route::get('/top-municipalities',   [AnalyticsController::class, 'topMunicipalities']);
                Route::get('/top-spots',            [AnalyticsController::class, 'topSpots']);
                Route::get('/chart-data',           [AnalyticsController::class, 'chartData']);
                Route::get('/monthly-trend',        [AnalyticsController::class, 'monthlyTrend']);
                Route::get('/filter-options',       [AnalyticsController::class, 'filterOptions']);
                Route::get('/full',                 [AnalyticsController::class, 'full']);
                Route::get('/export',               [AnalyticsController::class, 'export']);
            });

        // Fare Data (full access)
        Route::prefix('fare-data')->group(function () {
            Route::get('/stats',              [FareDataController::class, 'stats']);
            Route::get('/guides',             [FareDataController::class, 'guides']);
            Route::get('/matrices',           [FareDataController::class, 'matrices']);
            Route::get('/uploads',            [FareDataController::class, 'uploads']);
            Route::get('/import-logs',        [FareDataController::class, 'importLogs']);
            Route::get('/validation-errors',  [FareDataController::class, 'validationErrors']);
            Route::post('/upload',            [FareDataController::class, 'upload']);
            Route::post('/sync',              [FareDataController::class, 'sync']);
            Route::delete('/{id}',            [FareDataController::class, 'destroy']);
        });

        // User Management (full CRUD)
        Route::prefix('users')->group(function () {
            Route::get('/',                  [UserController::class, 'index']);
            Route::get('/municipalities',    [UserController::class, 'municipalities']);
            Route::get('/audit-logs',        [UserController::class, 'auditLogs']);
            Route::get('/{id}',              [UserController::class, 'show']);
            Route::post('/',                 [UserController::class, 'store']);
            Route::put('/{id}',              [UserController::class, 'update']);
            Route::patch('/{id}/status',     [UserController::class, 'toggleStatus']);
            Route::patch('/{id}/password',   [UserController::class, 'resetPassword']);
        });

        // Archive Management
        Route::prefix('archive')->group(function () {
            Route::get('/stats',            [ArchiveManagementController::class, 'stats']);
            Route::get('/fares',            [ArchiveManagementController::class, 'archivedFares']);
            Route::get('/fares/{id}',       [ArchiveManagementController::class, 'archivedFareDetail']);
            Route::post('/fares/{id}/restore',  [ArchiveManagementController::class, 'restore']);
            Route::delete('/fares/{id}',    [ArchiveManagementController::class, 'permanentDelete']);
        });

        // Reports
        Route::get('/reports', [ReportGeneratorController::class, 'index']);

        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('/profile',          [SettingsController::class, 'profile']);
            Route::put('/profile',          [SettingsController::class, 'updateProfile']);
            Route::put('/password',         [SettingsController::class, 'updatePassword']);
        });
    });

    // ─────────────────────────────────────────────────────────────────────────
    //  LUPTO (lupto role)
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('lupto')->middleware('role:lupto')->group(function () {
        // Dashboard
        Route::get('/dashboard',                    [DashboardController::class, 'index']);
        Route::get('/dashboard/pending-spots',      [DashboardController::class, 'pendingSpots']);
        Route::post('/dashboard/approve-spot',      [DashboardController::class, 'approveSpot']);
        Route::post('/dashboard/reject-spot',       [DashboardController::class, 'rejectSpot']);
        Route::post('/dashboard/batch-approve-spots',[DashboardController::class,'batchApproveSpots']);

        // Map - for LUPTO to see all municipalities
        Route::get('/map', [MapController::class, 'luptoMapData']);

        // Tourist Spots (alias to shared controller for map-view.php)
        Route::get('/tourist-spots',     [TouristSpotController::class, 'index']);
        Route::get('/tourist-spots/{id}',[TouristSpotController::class, 'show']);

        // Analytics (read-only)
        Route::prefix('analytics')->group(function () {
            Route::get('/summary',              [AnalyticsController::class, 'summary']);
            Route::get('/top-municipalities',   [AnalyticsController::class, 'topMunicipalities']);
            Route::get('/top-spots',            [AnalyticsController::class, 'topSpots']);
            Route::get('/chart-data',           [AnalyticsController::class, 'chartData']);
            Route::get('/monthly-trend',        [AnalyticsController::class, 'monthlyTrend']);
            Route::get('/filter-options',       [AnalyticsController::class, 'filterOptions']);
            Route::get('/full',                 [AnalyticsController::class, 'full']);
            Route::get('/export',               [AnalyticsController::class, 'export']);
        });

        // Fare Data (view-only)
        Route::prefix('fare-data')->group(function () {
            Route::get('/guides',             [FareDataController::class, 'guides']);
            Route::get('/matrices',           [FareDataController::class, 'matrices']);
            Route::get('/uploads',            [FareDataController::class, 'uploads']);
            Route::get('/import-logs',        [FareDataController::class, 'importLogs']);
            Route::get('/validation-errors',  [FareDataController::class, 'validationErrors']);
        });

        // Leaderboard
        Route::prefix('leaderboard')->group(function () {
            Route::get('/',       [LeaderboardController::class, 'index']);
            Route::get('/top3',   [LeaderboardController::class, 'top3']);
            Route::get('/kpis',   [LeaderboardController::class, 'kpis']);
        });

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);

        // Reports
        Route::get('/reports', [ReportGeneratorController::class, 'index']);

        // User Management (view + basic update)
        Route::prefix('users')->group(function () {
            Route::get('/',               [UserController::class, 'index']);
            Route::put('/{id}',           [UserController::class, 'update']);
            Route::patch('/{id}/password',[UserController::class, 'resetPassword']);
        });

        // Merchandise Management
        Route::prefix('merch')->group(function () {
            Route::get('/inventory',        [MerchandiseController::class, 'getAdminInventory']);
            Route::post('/inventory',       [MerchandiseController::class, 'saveItem']);
            Route::delete('/inventory/{id}',[MerchandiseController::class, 'deleteItem']);
            Route::get('/reservations',     [MerchandiseController::class, 'getAdminReservations']);
            Route::patch('/reservations/{id}/claim', [MerchandiseController::class, 'claimReservation']);
        });
    });

    // ─────────────────────────────────────────────────────────────────────────
    //  MUNICIPAL (all *_mto + 'municipal' roles)
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('municipal')->middleware('role:municipal')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Analytics (scoped to own municipality)
        Route::prefix('analytics')->group(function () {
            Route::get('/summary',              [AnalyticsController::class, 'summary']);
            Route::get('/top-municipalities',   [AnalyticsController::class, 'topMunicipalities']);
            Route::get('/top-spots',            [AnalyticsController::class, 'topSpots']);
            Route::get('/chart-data',           [AnalyticsController::class, 'chartData']);
            Route::get('/monthly-trend',        [AnalyticsController::class, 'monthlyTrend']);
            Route::get('/filter-options',       [AnalyticsController::class, 'filterOptions']);
            Route::get('/full',                 [AnalyticsController::class, 'full']);
            Route::get('/export',               [AnalyticsController::class, 'export']);
        });

        // Fare Data (upload + view)
        Route::prefix('fare-data')->group(function () {
            Route::get('/guides',             [FareDataController::class, 'guides']);
            Route::get('/matrices',           [FareDataController::class, 'matrices']);
            Route::get('/uploads',            [FareDataController::class, 'uploads']);
            Route::get('/import-logs',        [FareDataController::class, 'importLogs']);
            Route::get('/validation-errors',  [FareDataController::class, 'validationErrors']);
            Route::post('/upload',            [FareDataController::class, 'upload']);
            Route::post('/sync',              [FareDataController::class, 'sync']);
        });

        // Tourist Spots (CRUD scoped to own municipality)
        Route::prefix('tourist-spots')->group(function () {
            Route::get('/',               [TouristSpotController::class, 'index']);
            Route::get('/{id}',           [TouristSpotController::class, 'show']);
            Route::post('/upload-image',  [TouristSpotController::class, 'uploadImage']);
            Route::post('/',              [TouristSpotController::class, 'store']);
            Route::put('/{id}',           [TouristSpotController::class, 'update']);
            Route::delete('/{id}',        [TouristSpotController::class, 'destroy']);
        });

        // Map
        Route::get('/map', [MapController::class, 'municipalityData']);

        // User Management (view + update)
        Route::prefix('users')->group(function () {
            Route::get('/',               [UserController::class, 'index']);
            Route::put('/{id}',           [UserController::class, 'update']);
            Route::patch('/{id}/password',[UserController::class, 'resetPassword']);
        });

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);

        // Reports
        Route::get('/reports', [ReportGeneratorController::class, 'index']);

        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('/profile',  [SettingsController::class, 'profile']);
            Route::put('/profile',  [SettingsController::class, 'updateProfile']);
            Route::put('/password', [SettingsController::class, 'updatePassword']);
        });
    });
});

// ─────────────────────────────────────────────────────────────────────────────
//  PUBLIC routes (no auth required) — for mobile app unauthenticated features
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('public')->group(function () {
    Route::get('/map',         [MapController::class, 'publicMapData']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
});

// ─────────────────────────────────────────────────────────────────────────────
//  TOURIST (mobile app — Bearer token auth)
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('tourist')->middleware('tourist.auth')->group(function () {
    // Dashboard (profile, trending, saved places, recommendations)
    Route::get('/dashboard', [TouristDashboardController::class, 'index']);
    Route::get('/profile', [TouristProfileController::class, 'show']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);

    // Destinations / Saved Places (Favorites)
    Route::post('/destinations/{id}/favorite', [FavoriteController::class, 'toggle']);

    // Itineraries (Saved Trips)
    Route::get('/itineraries',              [ItineraryController::class, 'index']);
    Route::post('/itineraries',             [ItineraryController::class, 'store']);
    Route::patch('/itineraries/{id}/complete', [ItineraryController::class, 'markCompleted']);

    // Itinerary Items (Check-in)
    Route::patch('/itineraries/items/{id}/visit', [ItineraryItemController::class, 'visit']);
    Route::post('/itineraries/items/{id}/visit',  [ItineraryItemController::class, 'visit']);

    // Merchandise
    Route::get('/merch', [MerchandiseController::class, 'index']);
    Route::post('/merch/reserve', [MerchandiseController::class, 'reserve']);
});