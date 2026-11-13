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

        // Process untouched reviews older than 2-3 days
        if (class_exists(\App\Http\Controllers\Tourist\ItineraryController::class)) {
            \App\Http\Controllers\Tourist\ItineraryController::processUntouchedVisitReviews(2);
        }

        if (!$user) {
            return response()->json([
                'status'  => 'success',
                'success' => true,
                'data'    => [],
                'reviews' => (object)[]
            ]);
        }

        $feedbacks = SiteFeedback::where('user_id', $user->id)
            ->whereNotNull('tourist_spot_id')
            ->get(['tourist_spot_id', 'itinerary_item_id', 'rating', 'testimony', 'policy_recommendation', 'cleanliness_level', 'safety_level', 'created_at']);

        $reviewedIds = $feedbacks->pluck('tourist_spot_id')->map(fn($id) => (int) $id)->unique()->values()->toArray();
        $reviewsMap = $feedbacks->keyBy('tourist_spot_id');
        $reviewedItemIds = $feedbacks->pluck('itinerary_item_id')->filter()->map(fn($id) => (int) $id)->unique()->values()->toArray();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $reviewedIds,
            'reviewed_item_ids' => $reviewedItemIds,
            'reviews' => $reviewsMap
        ]);
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
                    : ['user_id' => $userId, 'tourist_spot_id' => $spotId];
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

        // Award gamification points based on spot classification ONLY IF THIS IS THE FIRST REVIEW FOR THIS DESTINATION
        $classificationStatus = $spot ? $spot->classification_status : null;
        $rewardPoints = \App\Models\Classification::getPointsForStatus($classificationStatus);
        $canonicalClassification = \App\Models\Classification::normalizeStatus($classificationStatus);

        $rewardAwarded = false;
        if ($user && $isFirstReview) {
            try {
                $user->increment('xp', $rewardPoints);
                $user->increment('completed_activities');
                \App\Models\UserPoint::awardPointsSafely(
                    $user->id,
                    $rewardPoints,
                    'feedback',
                    "Shared site testimony and policy feedback for " . ($spot ? $spot->name : 'Destination') . " ({$canonicalClassification})",
                    $spotId ? (int) $spotId : null
                );
                $newXp = (int) ($user->fresh()->xp ?? 0);
                $newLevel = (int) floor($newXp / 1000) + 1;
                if ($user->level !== $newLevel) {
                    $user->update(['level' => $newLevel]);
                }
                $rewardAwarded = true;
            } catch (\Throwable $e) {}
        }

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
            'message'              => $rewardAwarded
                ? "Thank you for your testimony and feedback! (+{$rewardPoints} Points & +{$rewardPoints} XP earned — {$canonicalClassification})"
                : 'Review updated successfully! (Rewards have already been claimed for this destination)',
            'data'                 => $feedback,
            'spot_rating'          => isset($spot) && $spot ? (float) $spot->rating : ($rating ? (float) $rating : 0.0)
        ]);
    }
}
