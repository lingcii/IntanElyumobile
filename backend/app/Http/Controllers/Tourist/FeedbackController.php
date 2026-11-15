<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\SiteFeedback;
use App\Models\TouristSpot;
use App\Models\ItineraryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FeedbackController extends Controller
{
    /**
     * GET /api/tourist/feedback
     * GET /api/public/feedback
     * Returns testimonies and policy recommendations, optionally filtered by tourist_spot_id.
     */
    public function index(Request $request): JsonResponse
    {
        $spotId = $request->query('tourist_spot_id') 
            ?: $request->query('spot_id') 
            ?: $request->query('destination_id');

        $cacheKey = $spotId ? 'feedback:spot:' . (int) $spotId : null;
        if ($cacheKey && ($cached = Cache::get($cacheKey))) {
            return response()->json($cached);
        }

        $query = SiteFeedback::with('user:id,name,avatar')->latest();

        if ($spotId) {
            $query->where('tourist_spot_id', $spotId);
        }

        $feedbacks = $query->limit($spotId ? 20 : 50)->get();

        // Calculate summary metrics if spot ID is provided
        $summary = null;
        if ($spotId) {
            $spot = TouristSpot::find($spotId);
            
            $metrics = SiteFeedback::where('tourist_spot_id', $spotId)
                ->selectRaw("COUNT(*) as total_reviews, AVG(rating) as average_rating,
                    SUM(CASE WHEN cleanliness_level = 'clean' THEN 1 ELSE 0 END) as clean_count,
                    SUM(CASE WHEN cleanliness_level = 'moderate' THEN 1 ELSE 0 END) as moderate_clean_count,
                    SUM(CASE WHEN cleanliness_level = 'dirty' THEN 1 ELSE 0 END) as dirty_count,
                    SUM(CASE WHEN safety_level = 'safe' THEN 1 ELSE 0 END) as safe_count,
                    SUM(CASE WHEN safety_level = 'moderate' THEN 1 ELSE 0 END) as moderate_safe_count,
                    SUM(CASE WHEN safety_level = 'unsafe' THEN 1 ELSE 0 END) as unsafe_count")
                ->first();

            $totalReviews = (int) ($metrics->total_reviews ?? 0);
            $avgRating = $metrics->average_rating ?? null;

            $classStatus = $spot ? $spot->classification_status : 'EXIST';
            $canonClass = \App\Models\Classification::normalizeStatus($classStatus);
            $rewardPts = \App\Models\Classification::getPointsForStatus($classStatus);

            $summary = [
                'average_rating'        => $avgRating ? round((float) $avgRating, 1) : ($spot ? round((float) $spot->rating, 1) : 0.0),
                'total_reviews'         => $totalReviews,
                'classification_status' => $classStatus,
                'classification_name'   => $canonClass,
                'reward_points'         => $rewardPts,
                'cleanliness' => [
                    'clean'    => (int) ($metrics->clean_count ?? 0),
                    'moderate' => (int) ($metrics->moderate_clean_count ?? 0),
                    'dirty'    => (int) ($metrics->dirty_count ?? 0),
                ],
                'safety' => [
                    'safe'     => (int) ($metrics->safe_count ?? 0),
                    'moderate' => (int) ($metrics->moderate_safe_count ?? 0),
                    'unsafe'   => (int) ($metrics->unsafe_count ?? 0),
                ]
            ];
        }

        $payload = [
            'status'  => 'success',
            'success' => true,
            'data'    => $feedbacks,
            'summary' => $summary
        ];
        if ($cacheKey) Cache::put($cacheKey, $payload, now()->addMinutes(5));

        return response()->json($payload);
    }

    /**
     * GET /api/tourist/feedback/user-reviewed-spots
     * GET /api/public/feedback/user-reviewed-spots
     * Return list of destination spot IDs reviewed by the current user and their review content.
     */
    public function userReviewedSpots(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            $token = $request->bearerToken();
            if ($token) {
                $user = \App\Models\User::where('api_token', $token)->first();
            }
        }
        if (!$user) {
            $defaultUser = \App\Models\User::where('role', 'tourist')->first();
            $user = $defaultUser;
        }

        if (!$user) {
            return response()->json([
                'status'  => 'success',
                'success' => true,
                'data'    => [],
                'reviewed_item_ids' => [],
                'reviews' => (object)[],
                'item_reviews' => (object)[]
            ]);
        }

        // Auto-process untouched reviews scoped to this user at most once per 5 min
        if (
            Cache::add("untouched_reviews_sync:{$user->id}", 1, 300)
            && class_exists(\App\Http\Controllers\Tourist\ItineraryController::class)
        ) {
            \App\Http\Controllers\Tourist\ItineraryController::processUntouchedVisitReviews(2, $user->id);
        }

        $payload = Cache::remember("feedback:user:v2:{$user->id}", 300, function () use ($user) {
            $feedbacks = SiteFeedback::where('user_id', $user->id)
                ->whereNotNull('tourist_spot_id')
                ->with('touristSpot:id,name')
                ->latest()
                ->get(['tourist_spot_id', 'itinerary_item_id', 'rating', 'testimony', 'policy_recommendation', 'cleanliness_level', 'safety_level', 'created_at']);

            $reviewedIds = $feedbacks->pluck('tourist_spot_id')->map(fn($id) => (int) $id)->unique()->values()->toArray();
            $reviewedItemIds = $feedbacks->pluck('itinerary_item_id')->filter()->map(fn($id) => (int) $id)->unique()->values()->toArray();

            // Gather verified check-ins across itinerary items, user_points, and activity_logs
            $checkedInSpots = [];
            try {
                $visitedItems = DB::table('itinerary_items')
                    ->join('itineraries', 'itinerary_items.itinerary_id', '=', 'itineraries.id')
                    ->leftJoin('tourist_spots', 'itinerary_items.tourist_spot_id', '=', 'tourist_spots.id')
                    ->where('itineraries.user_id', $user->id)
                    ->whereNotNull('itinerary_items.tourist_spot_id')
                    ->where(function ($q) {
                        $q->where('itinerary_items.is_visited', true)
                          ->orWhereNotNull('itinerary_items.visited_at');
                    })
                    ->orderByDesc('itinerary_items.visited_at')
                    ->get([
                        'itinerary_items.tourist_spot_id',
                        'itinerary_items.visited_at',
                        'itinerary_items.updated_at',
                        'tourist_spots.name as spot_name',
                    ]);
                foreach ($visitedItems as $vi) {
                    $sid = (int) $vi->tourist_spot_id;
                    if ($sid <= 0 || isset($checkedInSpots[$sid])) continue;
                    $checkedInSpots[$sid] = [
                        'tourist_spot_id' => $sid,
                        'spot_name' => $vi->spot_name,
                        'created_at' => $vi->visited_at ?: $vi->updated_at,
                    ];
                }
            } catch (\Throwable $e) {}

            try {
                $pointCheckins = DB::table('user_points')
                    ->leftJoin('tourist_spots', 'user_points.spot_id', '=', 'tourist_spots.id')
                    ->where('user_points.user_id', $user->id)
                    ->whereIn('user_points.source', ['check_in', 'ar_checkin'])
                    ->whereNotNull('user_points.spot_id')
                    ->orderByDesc('user_points.created_at')
                    ->get([
                        'user_points.spot_id',
                        'user_points.created_at',
                        'tourist_spots.name as spot_name',
                    ]);
                foreach ($pointCheckins as $pc) {
                    $sid = (int) $pc->spot_id;
                    if ($sid <= 0 || isset($checkedInSpots[$sid])) continue;
                    $checkedInSpots[$sid] = [
                        'tourist_spot_id' => $sid,
                        'spot_name' => $pc->spot_name,
                        'created_at' => $pc->created_at,
                    ];
                }
            } catch (\Throwable $e) {}

            $checkinLogDetails = [];
            try {
                $checkinLogDetails = DB::table('activity_logs')
                    ->where('user_id', $user->id)
                    ->where('action', 'Points Awarded')
                    ->where(function ($q) {
                        $q->where('details', 'like', '%Check-in at%')
                          ->orWhere('details', 'like', '%Check In%');
                    })
                    ->orderByDesc('created_at')
                    ->get(['details', 'created_at'])
                    ->all();
            } catch (\Throwable $e) {}

            $reviewsMap = [];
            $itemReviewsMap = [];
            $combinedMap = [];
            foreach ($feedbacks as $fb) {
                $spotId = (int) $fb->tourist_spot_id;
                $spotIdKey = (string) $spotId;
                $spotName = $fb->touristSpot?->name;
                $hasReviewText = trim((string) ($fb->testimony ?? '')) !== '' || trim((string) ($fb->policy_recommendation ?? '')) !== '';
                $hasCheckinLog = false;
                if ($spotName && !empty($checkinLogDetails)) {
                    foreach ($checkinLogDetails as $log) {
                        if (stripos((string) $log->details, "at {$spotName}") !== false) {
                            $hasCheckinLog = true;
                            break;
                        }
                    }
                }

                $isCheckedIn = isset($checkedInSpots[$spotId]) || $hasCheckinLog || !$hasReviewText;
                $isReviewed = $hasReviewText || (!$isCheckedIn);

                if ($fb->itinerary_item_id) {
                    $itemIdKey = (string) ((int) $fb->itinerary_item_id);
                    if (!isset($itemReviewsMap[$itemIdKey])) {
                        $itemArr = $fb->toArray();
                        $itemArr['spot_name'] = $spotName;
                        $itemArr['is_reviewed'] = $isReviewed;
                        $itemArr['is_checked_in'] = $isCheckedIn;
                        unset($itemArr['tourist_spot']);
                        $itemReviewsMap[$itemIdKey] = $itemArr;
                    }
                }

                if (!isset($reviewsMap[$spotIdKey])) {
                    $arr = $fb->toArray();
                    $arr['spot_name'] = $spotName;
                    $arr['is_reviewed'] = $isReviewed;
                    $arr['is_checked_in'] = $isCheckedIn;
                    unset($arr['tourist_spot']);
                    $reviewsMap[$spotIdKey] = $arr;
                    $combinedMap[$spotIdKey] = $arr;
                } else {
                    if ($isReviewed) $combinedMap[$spotIdKey]['is_reviewed'] = true;
                    if ($isCheckedIn) $combinedMap[$spotIdKey]['is_checked_in'] = true;
                }
            }

            foreach ($checkedInSpots as $sid => $ci) {
                $spotIdKey = (string) $sid;
                if (!isset($combinedMap[$spotIdKey])) {
                    $combinedMap[$spotIdKey] = [
                        'tourist_spot_id' => $sid,
                        'spot_name' => $ci['spot_name'],
                        'rating' => 5,
                        'testimony' => null,
                        'created_at' => $ci['created_at'],
                        'is_reviewed' => false,
                        'is_checked_in' => true,
                    ];
                } else {
                    $combinedMap[$spotIdKey]['is_checked_in'] = true;
                }
            }

            return [
                'status'  => 'success',
                'success' => true,
                'data'    => $reviewedIds,
                'reviewed_item_ids' => $reviewedItemIds,
                'reviews' => (object) $reviewsMap,
                'item_reviews' => (object) $itemReviewsMap,
                'reviews_and_checkins' => array_values($combinedMap),
            ];
        });

        return response()->json($payload);
    }

    /**
     * POST /api/tourist/feedback
     * POST /api/public/feedback
     * Submit a testimony and/or policy recommendation, updating spot rating in real time.
     * Rewards (+25 XP, +25 Points) are awarded ONCE per destination per user.
     */
    public function store(Request $request): JsonResponse
    {
        $spotId = $request->input('tourist_spot_id') 
            ?: $request->input('spot_id') 
            ?: $request->input('destination_id');

        $rating = $request->input('rating') !== null ? (int) $request->input('rating') : null;
        if ($rating !== null) {
            $rating = min(max($rating, 1), 5);
        }

        $user = $request->user();
        $userId = $user ? $user->id : null;
        $itineraryItemId = $request->filled('itinerary_item_id') ? (int) $request->input('itinerary_item_id') : null;

        if (!$userId) {
            $token = $request->bearerToken();
            if ($token) {
                $foundUser = \App\Models\User::where('api_token', $token)->first();
                if ($foundUser) {
                    $user = $foundUser;
                    $userId = $foundUser->id;
                }
            }
        }

        if ($itineraryItemId) {
            $reviewItem = ItineraryItem::whereKey($itineraryItemId)
                ->whereHas('itinerary', fn($query) => $query->where('user_id', $userId))
                ->first();
            if (!$reviewItem || (int) $reviewItem->tourist_spot_id !== (int) $spotId) {
                return response()->json(['message' => 'This trip stop cannot be reviewed by this account.'], 422);
            }
        }

        // If user is not authenticated, fallback to default user if available
        if (!$userId) {
            $defaultUser = \App\Models\User::where('role', 'tourist')->first();
            $userId = $defaultUser ? $defaultUser->id : null;
            if ($userId) {
                $user = $defaultUser;
            }
        }

        // Anti-abuse check: verify if the user has ALREADY reviewed this specific destination
        $isFirstReview = true;
        if ($userId && $spotId) {
            $alreadyReviewed = SiteFeedback::where('user_id', $userId)
                ->where('tourist_spot_id', $spotId)
                ->exists();
            if ($alreadyReviewed) {
                $isFirstReview = false;
            }
        }

        $spot = null;
        if ($spotId) {
            $feedbackData = array_filter([
                'rating'                => $rating,
                'testimony'             => $request->input('testimony'),
                'policy_recommendation' => $request->input('policy_recommendation'),
                'cleanliness_level'     => $request->input('cleanliness_level') ?: 'clean',
                'safety_level'          => $request->input('safety_level') ?: 'safe',
            ], fn($v) => !is_null($v));

            if ($userId) {
                $feedbackKey = $itineraryItemId
                    ? ['user_id' => $userId, 'itinerary_item_id' => $itineraryItemId]
                    : ['user_id' => $userId, 'tourist_spot_id' => $spotId, 'itinerary_item_id' => null];
                $feedback = SiteFeedback::updateOrCreate(
                    $feedbackKey,
                    array_merge($feedbackData, [
                        'tourist_spot_id' => $spotId,
                        'itinerary_item_id' => $itineraryItemId,
                    ])
                );
            } else {
                $feedback = SiteFeedback::create(array_merge([
                    'user_id'         => null,
                    'tourist_spot_id' => $spotId,
                ], $feedbackData));
            }

            // Immediately recalculate average rating for the destination spot
            $spot = TouristSpot::find($spotId);
            if ($spot) {
                $avgRating = SiteFeedback::where('tourist_spot_id', $spot->id)
                    ->whereNotNull('rating')
                    ->avg('rating');

                $newRating = $avgRating ? round((float) $avgRating, 1) : ($rating ? (float) $rating : 5.0);
                $spot->rating = $newRating;
                $spot->save();
            }
        } else {
            $feedback = SiteFeedback::create([
                'user_id'               => $userId,
                'tourist_spot_id'       => null,
                'rating'                => $rating,
                'testimony'             => $request->input('testimony'),
                'policy_recommendation' => $request->input('policy_recommendation'),
                'cleanliness_level'     => $request->input('cleanliness_level') ?: 'clean',
                'safety_level'          => $request->input('safety_level') ?: 'safe',
            ]);
        }

        // Invalidate map & dashboard caches
        \Illuminate\Support\Facades\Cache::forget('map:public:spots');
        \Illuminate\Support\Facades\Cache::forget('trending:top:5');
        \Illuminate\Support\Facades\Cache::forget('trending:top:10');
        \Illuminate\Support\Facades\Cache::forget('trending:top:50');
        if ($spotId) {
            Cache::forget('feedback:spot:' . (int) $spotId);
        }
        if ($userId) {
            Cache::forget("feedback:user:{$userId}");
            Cache::forget("feedback:user:v2:{$userId}");
        }

        // Award gamification points based on spot classification ONLY IF THIS IS THE FIRST REVIEW FOR THIS DESTINATION
        $classificationStatus = $spot ? $spot->classification_status : null;
        $rewardPoints = \App\Models\Classification::getPointsForStatus($classificationStatus);
        $canonicalClassification = \App\Models\Classification::normalizeStatus($classificationStatus);

        $rewardAwarded = false;
        // Points/XP are not awarded on review submission.

        return response()->json([
            'status'               => 'success',
            'success'              => true,
            'reward_awarded'       => $rewardAwarded,
            'earned_xp'            => $rewardAwarded ? $rewardPoints : 0,
            'earned_points'        => $rewardAwarded ? $rewardPoints : 0,
            'total_points'         => (int) ($user ? ($user->fresh()->points ?? 0) : 0),
            'xp'                   => (int) ($user ? ($user->fresh()->xp ?? 0) : 0),
            'completed_activities' => (int) ($user ? ($user->fresh()->completed_activities ?? 0) : 0),
            'classification'       => $canonicalClassification,
            'message'              => 'Thank you for your testimony and feedback!',
            'data'                 => $feedback,
            'spot_rating'          => isset($spot) && $spot ? (float) $spot->rating : ($rating ? (float) $rating : 0.0)
        ]);
    }
}
