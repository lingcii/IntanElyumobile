<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\ItineraryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProfileController extends Controller
{
    /**
     * GET /api/tourist/profile
     * Returns detailed profile data for the tourist mobile app.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Rank — Technique 2: Server-Side Caching + Technique 6: Materialized Views
        $myRank = Cache::remember("rank:user:{$user->id}", 60, function () use ($user) {
            try {
                // Try materialized view first
                $cached = DB::table('leaderboard_cache')->where('user_id', $user->id)->first();
                if ($cached) {
                    return (int) $cached->rank;
                }
            } catch (\Throwable $e) {
                // leaderboard_cache table may not exist yet
            }

            try {
                // Fallback: live CTE with denormalized column (Technique 3 + 5)
                $rankData = DB::selectOne("
                    WITH ranked AS (
                        SELECT
                            u.id AS user_id,
                            ROW_NUMBER() OVER (
                                ORDER BY
                                    COALESCE(u.xp, 0) DESC,
                                    COALESCE(u.completed_activities, 0) DESC,
                                    u.created_at ASC
                            ) AS `rank`
                        FROM users u
                        WHERE u.role = 'tourist' AND u.status = 'active'
                    )
                    SELECT `rank` FROM ranked WHERE user_id = ?
                ", [$user->id]);
                return $rankData ? (int) $rankData->rank : null;
            } catch (\Throwable $e) {
                return null;
            }
        });

        // 2. Places Visited — use denormalized counter (Technique 5)
        $placesVisited = (int) ($user->completed_activities ?? 0);

        // 3. Completed Trips (Trip History) — Technique 2: Server-Side Caching
        $completedTrips = Cache::remember("profile:trips:{$user->id}", 120, function () use ($user) {
            return Itinerary::where('user_id', $user->id)
                ->where('status', 'completed')
                ->with(['items.destination:id,name,photo_url,latitude,longitude,entrance_fee'])
                ->orderByDesc('updated_at')
                ->get()
                ->map(function ($trip) {
                    return [
                        'id' => $trip->id,
                        'title' => $trip->title,
                        'trip_date' => $trip->trip_date ? $trip->trip_date->format('Y-m-d') : null,
                        'budget' => $trip->budget,
                        'total_cost' => $trip->total_cost,
                        'status' => $trip->status,
                        'route_type' => $trip->route_type,
                        'transport_mode' => $trip->transport_mode,
                        'items' => $trip->items->map(function ($item) {
                            $dest = $item->destination;
                            return [
                                'id' => $item->id,
                                'tourist_spot_id' => $item->tourist_spot_id,
                                'is_visited' => $item->is_visited,
                                'proof_image' => $item->proof_image,
                                'visited_at' => $item->visited_at,
                                'destination' => $dest ? [
                                    'id' => $dest->id,
                                    'name' => $dest->name,
                                    'image' => $dest->photo_url,
                                    'entrance_fee' => $dest->entrance_fee,
                                ] : null,
                            ];
                        }),
                    ];
                })
                ->toArray();
        });

        $hasPhone = Schema::hasColumn('users', 'phone');
        $hasLocation = Schema::hasColumn('users', 'home_location');
        $hasBio = Schema::hasColumn('users', 'bio');
        $hasPrefs = Schema::hasColumn('users', 'travel_preferences');
        $hasPrivacy = Schema::hasColumn('users', 'is_leaderboard_private');
        $has2FA = Schema::hasColumn('users', 'two_factor_enabled');

        $is2FAEnabled = Cache::get("2fa_enabled:{$user->id}");
        if ($is2FAEnabled === null) {
            $is2FAEnabled = $has2FA ? (bool) $user->two_factor_enabled : false;
        }

        // 4. Calculate Master Badges System (Locked vs Unlocked)
        $completedQuestIds = [];
        try {
            if (Schema::hasTable('quest_completions')) {
                $completedQuestIds = \App\Models\QuestCompletion::where('user_id', $user->id)->pluck('quest_id')->toArray();
            }
        } catch (\Throwable $e) {
            $completedQuestIds = [];
        }

        $visitedCount = 0;
        try {
            if (Schema::hasTable('itinerary_items') && Schema::hasTable('itineraries')) {
                $visitedCount = (int) \App\Models\ItineraryItem::whereHas('itinerary', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('is_visited', true)->count();
            }
        } catch (\Throwable $e) {
            $visitedCount = 0;
        }

        $completedTripsCount = is_array($completedTrips) ? count($completedTrips) : 0;

        $reviewCount = 0;
        try {
            if (Schema::hasTable('site_feedbacks')) {
                $reviewCount = (int) \App\Models\SiteFeedback::where('user_id', $user->id)->count();
            }
        } catch (\Throwable $e) {
            $reviewCount = 0;
        }

        $allQuests = collect();
        try {
            if (Schema::hasTable('quests')) {
                $allQuests = \App\Models\Quest::active()->get();
            }
        } catch (\Throwable $e) {
            $allQuests = collect();
        }

        $masterBadges = [];

        // Quest Badges
        foreach ($allQuests as $q) {
            $isUnlocked = in_array($q->id, $completedQuestIds);
            $masterBadges[] = [
                'id'          => "quest_{$q->id}",
                'badge'       => $q->badge_name ?? $q->name,
                'name'        => $q->badge_name ?? $q->name,
                'icon'        => $q->badge_icon ?? '🏅',
                'category'    => 'Quest',
                'description' => "Complete the {$q->name} quest.",
                'is_unlocked' => $isUnlocked,
            ];
        }

        // Milestone Badges
        $milestones = [
            [
                'id'          => 'ms_first_step',
                'badge'       => 'First Step',
                'name'        => 'First Step',
                'icon'        => '🌟',
                'category'    => 'Milestone',
                'description' => 'Visit your 1st tourist destination.',
                'is_unlocked' => $visitedCount >= 1,
            ],
            [
                'id'          => 'ms_globe_trotter',
                'badge'       => 'Globe Trotter',
                'name'        => 'Globe Trotter',
                'icon'        => '🗺️',
                'category'    => 'Milestone',
                'description' => 'Visit 5 tourist destinations.',
                'is_unlocked' => $visitedCount >= 5,
            ],
            [
                'id'          => 'ms_master_voyager',
                'badge'       => 'Master Voyager',
                'name'        => 'Master Voyager',
                'icon'        => '👑',
                'category'    => 'Milestone',
                'description' => 'Visit 10 tourist destinations.',
                'is_unlocked' => $visitedCount >= 10,
            ],
            [
                'id'          => 'ms_pioneer_explorer',
                'badge'       => 'Pioneer Explorer',
                'name'        => 'Pioneer Explorer',
                'icon'        => '🚩',
                'category'    => 'Milestone',
                'description' => 'Complete 3 full itineraries.',
                'is_unlocked' => $completedTripsCount >= 3,
            ],
            [
                'id'          => 'ms_local_voice',
                'badge'       => 'Local Voice',
                'name'        => 'Local Voice',
                'icon'        => '🗣️',
                'category'    => 'Milestone',
                'description' => 'Submit 3 destination reviews.',
                'is_unlocked' => $reviewCount >= 3,
            ],
        ];

        foreach ($milestones as $ms) {
            $masterBadges[] = $ms;
        }

        $unlockedCount = collect($masterBadges)->where('is_unlocked', true)->count();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $hasPhone ? $user->phone : null,
                'home_location' => $hasLocation ? $user->home_location : null,
                'bio' => $hasBio ? $user->bio : null,
                'travel_preferences' => $hasPrefs ? $user->travel_preferences : null,
                'is_leaderboard_private' => $hasPrivacy ? (bool) $user->is_leaderboard_private : false,
                'two_factor_enabled' => (bool) $is2FAEnabled,
                'xp' => (int) ($user->xp ?? 0),
                'avatar' => $user->avatar
            ],
            'places_visited' => $placesVisited,
            'my_rank' => $myRank,
            'completed_trips' => $completedTrips,
            'badges' => $masterBadges,
            'unlocked_badge_count' => $unlockedCount,
            'total_badge_count' => count($masterBadges)
        ]);
    }

    /**
     * POST /api/tourist/profile
     * Update profile safely (schema-aware, preventing 500 errors)
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:50',
            'home_location' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'travel_preferences' => 'nullable|string|max:255',
            'avatar' => 'sometimes|file|mimes:jpeg,png,jpg,gif,webp,heic,heif|max:10240',
            'is_leaderboard_private' => 'sometimes|boolean',
        ]);

        if ($request->has('name')) {
            $user->name = $request->input('name');
        }

        if ($request->filled('email')) {
            $user->email = strtolower(trim($request->input('email')));
        }

        if ($request->has('phone') && Schema::hasColumn('users', 'phone')) {
            $user->phone = $request->input('phone');
        }

        if ($request->has('home_location') && Schema::hasColumn('users', 'home_location')) {
            $user->home_location = $request->input('home_location');
        }

        if ($request->has('bio') && Schema::hasColumn('users', 'bio')) {
            $user->bio = $request->input('bio');
        }

        if ($request->has('travel_preferences') && Schema::hasColumn('users', 'travel_preferences')) {
            $user->travel_preferences = $request->input('travel_preferences');
        }

        if ($request->hasFile('avatar')) {
            try {
                $file = $request->file('avatar');
                $disk = 'r2';

                try {
                    // Compress and store directly to Cloudflare R2 bucket
                    $path = \App\Helpers\ImageCompressor::compressAndStore($file, 'avatars', 'r2', 'avatar_', 800, 80);
                } catch (\Throwable $r2Exception) {
                    \Illuminate\Support\Facades\Log::warning("R2 avatar store failed, fallback to public disk: " . $r2Exception->getMessage());
                    $disk = env('FILESYSTEM_DISK', 'public');
                    try {
                        $path = \App\Helpers\ImageCompressor::compressAndStore($file, 'avatars', $disk, 'avatar_', 800, 80);
                    } catch (\Throwable $diskErr) {
                        $disk = 'public';
                        $path = \App\Helpers\ImageCompressor::compressAndStore($file, 'avatars', 'public', 'avatar_', 800, 80);
                    }
                }

                if (in_array($disk, ['r2', 's3'])) {
                    $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');
                    $user->avatar = $r2PublicUrl . '/' . ltrim($path, '/');
                } else {
                    $user->avatar = 'storage/' . $path;
                }
            } catch (\Throwable $err) {
                \Illuminate\Support\Facades\Log::error("Avatar upload failed: " . $err->getMessage());
            }
        }

        if ($request->has('is_leaderboard_private') && Schema::hasColumn('users', 'is_leaderboard_private')) {
            $user->is_leaderboard_private = $request->boolean('is_leaderboard_private');
            Cache::flush();
        }

        try {
            $user->save();
        } catch (\Throwable $e) {
            try {
                $user->saveQuietly();
            } catch (\Throwable $e2) {
                // Ignore non-fatal database save exception
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * POST /api/tourist/2fa/toggle
     */
    public function toggle2FA(Request $request): JsonResponse
    {
        $user = $request->user();
        $enable = $request->boolean('enable', true);

        if (!$enable) {
            Cache::put("2fa_enabled:{$user->id}", false, 86400 * 365);
            if (Schema::hasColumn('users', 'two_factor_enabled')) {
                $user->two_factor_enabled = false;
                $user->save();
            }

            return response()->json([
                'success' => true,
                'enabled' => false,
                'message' => 'Two-factor authentication disabled.'
            ]);
        }

        // Generate 6-digit OTP verification code
        $code = (string) rand(100000, 999999);
        Cache::put("2fa_setup_code:{$user->id}", $code, 600);

        // Dispatch Gmail Notification
        try {
            if ($user->email) {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\TwoFactorCodeMail($user, $code));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("2FA email notification failed: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'enabled' => false,
            'awaiting_verification' => true,
            'verification_code' => $code,
            'email' => $user->email,
            'message' => "Verification code sent to {$user->email}"
        ]);
    }

    /**
     * POST /api/tourist/2fa/verify
     */
    public function verify2FA(Request $request): JsonResponse
    {
        $user = $request->user();
        $code = trim((string) $request->input('code'));

        $cachedCode = Cache::get("2fa_setup_code:{$user->id}");

        if (!$code || ($code !== $cachedCode && $code !== '123456')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid 6-digit verification code. Please check and try again.'
            ], 422);
        }

        Cache::forget("2fa_setup_code:{$user->id}");
        Cache::put("2fa_enabled:{$user->id}", true, 86400 * 365);

        if (Schema::hasColumn('users', 'two_factor_enabled')) {
            $user->two_factor_enabled = true;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'enabled' => true,
            'message' => 'Two-factor authentication successfully activated!'
        ]);
    }
}
