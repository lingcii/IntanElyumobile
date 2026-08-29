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
     * POST /api/tourist/feedback
     * POST /api/public/feedback
     * Submit a testimony and/or policy recommendation, updating spot rating in real time.
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

        // If user is not authenticated, fallback to default user if available
        if (!$userId) {
            $defaultUser = \App\Models\User::where('role', 'tourist')->first();
            $userId = $defaultUser ? $defaultUser->id : null;
        }

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

        // Award gamification points (+25 XP, +25 points)
        if ($user) {
            try {
                $user->increment('xp', 25);
                $user->increment('completed_activities');
                \App\Models\UserPoint::awardPointsSafely(
                    $user->id,
                    25,
                    'feedback',
                    'Shared site testimony and policy feedback'
                );
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'status'      => 'success',
            'success'     => true,
            'message'     => 'Thank you for your testimony and feedback! (+25 XP & +25 Points earned)',
            'data'        => $feedback,
            'spot_rating' => isset($spot) && $spot ? (float) $spot->rating : ($rating ? (float) $rating : 5.0)
        ]);
    }
}
