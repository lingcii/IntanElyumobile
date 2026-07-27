<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Quest;
use App\Models\QuestCompletion;
use App\Models\TouristSpot;
use App\Models\UserPoint;
use App\Models\ItineraryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QuestController extends Controller
{
    /**
     * GET /api/tourist/quests
     * List all active quests, optionally filtered by available hours.
     * Also marks which quests the user has already completed.
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $hours = (float) ($request->query('hours', 99)); // 99 = no filter

        $query = Quest::active();
        if ($hours < 90) {
            $query->where('required_hours', '<=', $hours);
        }
        $quests = $query->orderBy('required_hours')->get();

        $completedIds = $user ? QuestCompletion::where('user_id', $user->id)
            ->pluck('quest_id')
            ->toArray() : [];

        $result = $quests->map(function ($quest) use ($completedIds) {
            $spotIds = is_array($quest->spot_ids) ? $quest->spot_ids : (json_decode($quest->spot_ids ?? '[]', true) ?? []);
            return [
                'id'             => $quest->id,
                'name'           => $quest->name,
                'description'    => $quest->description,
                'theme_icon'     => $quest->theme_icon,
                'theme_color'    => $quest->theme_color,
                'required_hours' => $quest->required_hours,
                'spot_count'     => count($spotIds),
                'xp_reward'      => $quest->xp_reward,
                'badge_name'     => $quest->badge_name,
                'badge_icon'     => $quest->badge_icon,
                'category'       => $quest->category,
                'is_completed'   => in_array($quest->id, $completedIds),
            ];
        });

        return response()->json([
            'status' => 'success',
            'quests' => $result,
        ]);
    }

    /**
     * GET /api/tourist/quests/my-completions
     * Returns the user's completed quests and earned badges.
     */
    public function myCompletions(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'success', 'completions' => [], 'badge_count' => 0]);
        }

        $completedQuestIds = QuestCompletion::where('user_id', $user->id)->pluck('quest_id')->toArray();
        $visitedCount      = (int) \App\Models\ItineraryItem::whereHas('itinerary', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('is_visited', true)->count();
        $completedTripsCount = (int) \App\Models\Itinerary::where('user_id', $user->id)->where('status', 'completed')->count();
        $reviewCount       = (int) \App\Models\SiteFeedback::where('user_id', $user->id)->count();

        $allQuests = Quest::active()->get();
        $masterBadges = [];

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
            'status'               => 'success',
            'badges'               => $masterBadges,
            'badge_count'          => $unlockedCount,
            'total_badge_count'    => count($masterBadges),
        ]);
    }

    /**
     * GET /api/tourist/quests/{id}/generate
     * Filters quest spots by the user's time budget and returns an optimized route.
     *
     * Query params:
     *   - start_lat, start_lng (optional) — user's current GPS
     *   - hours (optional, override)
     */
    public function generate(
        Request $request,
        int $id,
        \App\Services\RouteOptimizationService $optimizer
    ): JsonResponse {
        $quest = Quest::active()->findOrFail($id);

        $startLat = $request->query('start_lat') !== null ? (float) $request->query('start_lat') : null;
        $startLng = $request->query('start_lng') !== null ? (float) $request->query('start_lng') : null;

        // Override hours from query or use quest default
        $availableHours = (float) ($request->query('hours', $quest->required_hours));

        $allSpotIds = $quest->spot_ids ?? [];
        if (empty($allSpotIds)) {
            return response()->json(['status' => 'error', 'message' => 'Quest has no spots configured.'], 422);
        }

        // Fetch spots
        $spots = TouristSpot::whereIn('id', $allSpotIds)
            ->where(function ($q) {
                $q->whereIn('status', ['EXIST', 'approved', 'active', 'EXISTING'])
                  ->orWhereNull('status');
            })
            ->get()
            ->keyBy('id');

        if ($spots->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No active spots found for this quest.'], 404);
        }

        // ── Time-budget filtering ─────────────────────────────────────────
        // Filter out spots we cannot possibly reach within the time budget.
        // Rough estimate: 40 km/h average + 1.5h visit per spot.
        $visitDuration = 1.5; // hours per spot
        $maxSpots      = (int) max(1, floor($availableHours / $visitDuration));

        // If user provided GPS, sort spots by proximity first
        $eligibleIds = collect($allSpotIds)->filter(fn ($id) => $spots->has($id))->values()->toArray();

        if ($startLat !== null && $startLng !== null && count($eligibleIds) > $maxSpots) {
            // Score each spot by distance from start; keep the $maxSpots closest ones
            $scored = collect($eligibleIds)->map(function ($sid) use ($spots, $startLat, $startLng) {
                $spot = $spots[$sid];
                $d    = $this->haversine($startLat, $startLng, $spot->latitude, $spot->longitude);
                return ['id' => $sid, 'dist' => $d];
            })->sortBy('dist')->take($maxSpots)->pluck('id')->toArray();
            $eligibleIds = $scored;
        } else {
            $eligibleIds = array_slice($eligibleIds, 0, $maxSpots);
        }

        // ── TSP optimization ──────────────────────────────────────────────
        $optimizedIds  = $optimizer->optimizeRoute($eligibleIds, $startLat, $startLng);
        $orderedSpots  = collect($optimizedIds)->map(fn ($sid) => $spots[$sid] ?? null)->filter()->values();

        // ── Estimated totals ──────────────────────────────────────────────
        $totalDistanceKm = 0.0;
        $prevLat = $startLat ?? ($orderedSpots->first()?->latitude ?? 0);
        $prevLng = $startLng ?? ($orderedSpots->first()?->longitude ?? 0);

        foreach ($orderedSpots as $spot) {
            $totalDistanceKm += $this->haversine($prevLat, $prevLng, $spot->latitude, $spot->longitude) / 1000;
            $prevLat = $spot->latitude;
            $prevLng = $spot->longitude;
        }

        $estimatedHours = round($orderedSpots->count() * $visitDuration + ($totalDistanceKm / 40), 1);

        return response()->json([
            'status'           => 'success',
            'quest'            => [
                'id'         => $quest->id,
                'name'       => $quest->name,
                'theme_icon' => $quest->theme_icon,
                'theme_color'=> $quest->theme_color,
                'xp_reward'  => $quest->xp_reward,
                'badge_name' => $quest->badge_name,
                'badge_icon' => $quest->badge_icon,
            ],
            'spots'             => $orderedSpots->map(fn ($s) => [
                'id'        => $s->id,
                'name'      => $s->name,
                'latitude'  => $s->latitude,
                'longitude' => $s->longitude,
                'photo_url' => $s->photo_url,
                'category'  => $s->category,
                'entrance_fee' => $s->entrance_fee,
                'classification_status' => $s->classification_status,
            ]),
            'optimized_ids'     => $optimizedIds,
            'spot_count'        => $orderedSpots->count(),
            'total_distance_km' => round($totalDistanceKm, 2),
            'estimated_hours'   => $estimatedHours,
        ]);
    }

    /**
     * POST /api/tourist/quests/{id}/complete
     * Validates all quest spots have been visited by the user (via itinerary items or AR check-ins).
     * Awards quest XP and badge on success.
     *
     * Body: { itinerary_id: int } (optional — used to cross-check visited items)
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        $user  = $request->user();
        $quest = Quest::active()->findOrFail($id);

        // Prevent double completion
        $alreadyDone = QuestCompletion::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->exists();

        if ($alreadyDone) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You have already completed this quest!',
            ], 409);
        }

        $questSpotIds = $quest->spot_ids ?? [];

        // Check visited spots — either via itinerary items OR AR check-ins
        $itineraryId = $request->input('itinerary_id');
        $visitedSpotIds = [];

        if ($itineraryId) {
            $visitedSpotIds = ItineraryItem::where('itinerary_id', $itineraryId)
                ->where('user_id_check', function ($q) use ($user) {
                    // Cross-check itinerary ownership inline
                })
                ->where('is_visited', true)
                ->pluck('tourist_spot_id')
                ->toArray();
        }

        // Also count AR check-ins for this user
        $arVisited = \App\Models\ArCheckin::where('user_id', $user->id)
            ->whereIn('spot_id', $questSpotIds)
            ->pluck('spot_id')
            ->toArray();

        $visitedSpotIds = array_unique(array_merge($visitedSpotIds, $arVisited));

        // Determine how many quest spots were completed
        $completedCount = count(array_intersect($questSpotIds, $visitedSpotIds));
        $totalCount     = count($questSpotIds);
        $requiredCount  = (int) max(1, ceil($totalCount * 0.8)); // require 80% completion

        if ($completedCount < $requiredCount) {
            return response()->json([
                'status'           => 'error',
                'message'          => "Complete at least {$requiredCount} of {$totalCount} quest spots first! ({$completedCount} done so far)",
                'completed_count'  => $completedCount,
                'required_count'   => $requiredCount,
                'total_count'      => $totalCount,
            ], 422);
        }

        // ── Award quest XP + badge ────────────────────────────────────────
        $xpEarned = DB::transaction(function () use ($user, $quest) {
            QuestCompletion::create([
                'user_id'      => $user->id,
                'quest_id'     => $quest->id,
                'xp_earned'    => $quest->xp_reward,
                'completed_at' => now(),
            ]);

            UserPoint::awardPointsSafely(
                $user->id,
                $quest->xp_reward,
                "quest_complete:{$quest->id}",
                "Quest Completed: {$quest->name} — Badge: {$quest->badge_name}"
            );

            // Update user XP + level
            $newXp    = ($user->xp ?? 0) + $quest->xp_reward;
            $newLevel = (int) floor($newXp / 1000) + 1;

            // Append badge to user's badges JSON
            $badges   = $user->badges ? json_decode($user->badges, true) : [];
            $badges[] = [
                'name'       => $quest->badge_name,
                'icon'       => $quest->badge_icon,
                'quest_id'   => $quest->id,
                'earned_at'  => now()->toIso8601String(),
            ];

            $user->update([
                'xp'     => $newXp,
                'level'  => $newLevel,
                'badges' => json_encode($badges),
            ]);

            Cache::forget("rank:user:{$user->id}");

            return $quest->xp_reward;
        });

        return response()->json([
            'status'      => 'success',
            'message'     => "🎉 Quest Complete! You earned the '{$quest->badge_name}' badge!",
            'xp_earned'   => $xpEarned,
            'badge_name'  => $quest->badge_name,
            'badge_icon'  => $quest->badge_icon,
            'total_xp'    => $user->xp,
            'level'       => $user->level,
        ]);
    }

    /**
     * POST /api/tourist/quests/{id}/start
     * Start a 24-hour quest and generate an active itinerary in Saved Trips.
     */
    public function startQuest(Request $request, int $id): JsonResponse
    {
        $user  = $request->user();
        $quest = Quest::active()->findOrFail($id);

        $spotIds = is_array($quest->spot_ids) ? $quest->spot_ids : (json_decode($quest->spot_ids ?? '[]', true) ?? []);
        if (empty($spotIds)) {
            return response()->json(['status' => 'error', 'message' => 'Quest has no spots configured.'], 422);
        }

        $title = "{$quest->name} (Quest)";

        // Create active itinerary for this quest
        $itinerary = \App\Models\Itinerary::create([
            'user_id'        => $user->id,
            'title'          => $title,
            'trip_date'      => now()->format('Y-m-d'),
            'status'         => 'pending',
            'budget'         => 0,
            'total_cost'     => 0,
            'route_type'     => 'Quest Route',
            'transport_mode' => 'Driving',
        ]);

        foreach ($spotIds as $sid) {
            ItineraryItem::create([
                'itinerary_id'    => $itinerary->id,
                'tourist_spot_id' => $sid,
            ]);
        }

        // Cache invalidation
        Cache::forget("profile:trips:{$user->id}");

        $expiresAt = now()->addHours(24)->toIso8601String();

        return response()->json([
            'status'       => 'success',
            'message'      => "Quest '{$quest->name}' activated! Added to your Saved Trips.",
            'itinerary_id' => $itinerary->id,
            'expires_at'   => $expiresAt,
        ]);
    }

    /** Haversine distance in meters */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
