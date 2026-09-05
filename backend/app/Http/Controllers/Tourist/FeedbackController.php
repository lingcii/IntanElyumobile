<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\SiteFeedback;
use App\Models\TouristSpot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $query = SiteFeedback::with('user:id,name,avatar')
            ->latest();

        if ($spotId) {
            $query->where('tourist_spot_id', $spotId);
        }

        $feedbacks = $query->get();

        // Calculate summary metrics if spot ID is provided
        $summary = null;
        if ($spotId) {
            $spot = TouristSpot::find($spotId);
            
            // Calculate distributions
            $cleanlinessDistribution = SiteFeedback::where('tourist_spot_id', $spotId)
                ->select('cleanliness_level', DB::raw('count(*) as count'))
                ->whereNotNull('cleanliness_level')
                ->groupBy('cleanliness_level')
                ->pluck('count', 'cleanliness_level');

            $safetyDistribution = SiteFeedback::where('tourist_spot_id', $spotId)
                ->select('safety_level', DB::raw('count(*) as count'))
                ->whereNotNull('safety_level')
                ->groupBy('safety_level')
                ->pluck('count', 'safety_level');

            $totalReviews = SiteFeedback::where('tourist_spot_id', $spotId)->count();
            $avgRating = SiteFeedback::where('tourist_spot_id', $spotId)->whereNotNull('rating')->avg('rating');

            $summary = [
                'average_rating' => $avgRating ? round((float) $avgRating, 1) : ($spot ? round((float) $spot->rating, 1) : 5.0),
                'total_reviews'  => $totalReviews,
                'cleanliness' => [
                    'clean'    => (int) ($cleanlinessDistribution['clean'] ?? 0),
                    'moderate' => (int) ($cleanlinessDistribution['moderate'] ?? 0),
                    'dirty'    => (int) ($cleanlinessDistribution['dirty'] ?? 0),
                ],
                'safety' => [
                    'safe'     => (int) ($safetyDistribution['safe'] ?? 0),
                    'moderate' => (int) ($safetyDistribution['moderate'] ?? 0),
                    'unsafe'   => (int) ($safetyDistribution['unsafe'] ?? 0),
                ]
            ];
        }

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $feedbacks,
            'summary' => $summary
        ]);
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
                'reviews' => (object)[]
            ]);
        }

        $feedbacks = SiteFeedback::where('user_id', $user->id)
            ->whereNotNull('tourist_spot_id')
            ->get(['tourist_spot_id', 'rating', 'testimony', 'policy_recommendation', 'cleanliness_level', 'safety_level', 'created_at']);

        $reviewedIds = $feedbacks->pluck('tourist_spot_id')->map(fn($id) => (int) $id)->unique()->values()->toArray();
        $reviewsMap = $feedbacks->keyBy('tourist_spot_id');

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $reviewedIds,
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
                $feedback = SiteFeedback::updateOrCreate(
                    [
                        'user_id'         => $userId,
                        'tourist_spot_id' => $spotId,
                    ],
                    $feedbackData
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

        // Award gamification points (+25 XP, +25 points) ONLY IF THIS IS THE FIRST REVIEW FOR THIS DESTINATION
        $rewardAwarded = false;
        if ($user && $isFirstReview) {
            try {
                $user->increment('xp', 25);
                $user->increment('completed_activities');
                \App\Models\UserPoint::awardPointsSafely(
                    $user->id,
                    25,
                    'feedback',
                    'Shared site testimony and policy feedback' . ($spot ? ' for ' . $spot->name : ''),
                    $spotId ? (int) $spotId : null
                );
                $rewardAwarded = true;
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'status'         => 'success',
            'success'        => true,
            'reward_awarded' => $rewardAwarded,
            'earned_xp'      => $rewardAwarded ? 25 : 0,
            'earned_points'  => $rewardAwarded ? 25 : 0,
            'message'        => $rewardAwarded
                ? 'Thank you for your testimony and feedback! (+25 XP & +25 Points earned 🎉)'
                : 'Review updated successfully! (Rewards have already been claimed for this destination)',
            'data'           => $feedback,
            'spot_rating'    => isset($spot) && $spot ? (float) $spot->rating : ($rating ? (float) $rating : 5.0)
        ]);
    }
}
