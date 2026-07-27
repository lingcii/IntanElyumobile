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
     * Returns testimonies and policy recommendations, optionally filtered by tourist_spot_id.
     */
    public function index(Request $request): JsonResponse
    {
        $spotId = $request->query('tourist_spot_id');

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

            $summary = [
                'average_rating' => $spot ? round($spot->rating, 1) : 0,
                'total_reviews' => SiteFeedback::where('tourist_spot_id', $spotId)->count(),
                'cleanliness' => [
                    'clean' => (int)($cleanlinessDistribution['clean'] ?? 0),
                    'moderate' => (int)($cleanlinessDistribution['moderate'] ?? 0),
                    'dirty' => (int)($cleanlinessDistribution['dirty'] ?? 0),
                ],
                'safety' => [
                    'safe' => (int)($safetyDistribution['safe'] ?? 0),
                    'moderate' => (int)($safetyDistribution['moderate'] ?? 0),
                    'unsafe' => (int)($safetyDistribution['unsafe'] ?? 0),
                ]
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $feedbacks,
            'summary' => $summary
        ]);
    }

    /**
     * POST /api/tourist/feedback
     * Submit a testimony and/or policy recommendation.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tourist_spot_id' => 'nullable|integer|exists:tourist_spots,id',
            'rating' => 'nullable|integer|between:1,5',
            'testimony' => 'nullable|string',
            'policy_recommendation' => 'nullable|string',
            'cleanliness_level' => 'nullable|string|in:clean,moderate,dirty',
            'safety_level' => 'nullable|string|in:safe,moderate,unsafe',
        ]);

        $user = $request->user();

        // Enforce 1 review per user per spot (update existing if re-submitted)
        if ($request->tourist_spot_id) {
            $feedbackData = array_filter([
                'rating'                => $request->rating,
                'testimony'             => $request->testimony,
                'policy_recommendation' => $request->policy_recommendation,
                'cleanliness_level'     => $request->cleanliness_level,
                'safety_level'          => $request->safety_level,
            ], fn($v) => !is_null($v));

            $feedback = SiteFeedback::updateOrCreate(
                [
                    'user_id'         => $user->id,
                    'tourist_spot_id' => $request->tourist_spot_id,
                ],
                $feedbackData
            );
        } else {
            // Deduplicate rapid general feedback submissions (within 10 seconds)
            $existingRecent = SiteFeedback::where('user_id', $user->id)
                ->whereNull('tourist_spot_id')
                ->where('created_at', '>=', now()->subSeconds(10))
                ->first();

            if ($existingRecent) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Feedback submitted successfully!',
                    'data'    => $existingRecent
                ]);
            }

            $feedback = SiteFeedback::create([
                'user_id'               => $user->id,
                'tourist_spot_id'       => null,
                'rating'                => $request->rating,
                'testimony'             => $request->testimony,
                'policy_recommendation' => $request->policy_recommendation,
                'cleanliness_level'     => $request->cleanliness_level,
                'safety_level'          => $request->safety_level,
            ]);
        }

        // If specific tourist spot feedback is given with a rating, recalculate average rating
        if ($request->tourist_spot_id && $request->rating) {
            $spot = TouristSpot::find($request->tourist_spot_id);
            if ($spot) {
                $avgRating = SiteFeedback::where('tourist_spot_id', $spot->id)
                    ->whereNotNull('rating')
                    ->avg('rating');
                $spot->rating = round((float)$avgRating, 2);
                $spot->save();
            }
        }

        // Invalidate map & dashboard caches so new ratings/testimonies reflect immediately
        \Illuminate\Support\Facades\Cache::forget('map:public:spots');
        \Illuminate\Support\Facades\Cache::forget('trending:top:5');
        \Illuminate\Support\Facades\Cache::forget('trending:top:10');
        \Illuminate\Support\Facades\Cache::forget('trending:top:50');

        // Award gamification points (+25 XP, +25 points)
        if ($user) {
            try {
                $user->increment('xp', 25);
                \App\Models\UserPoint::awardPointsSafely(
                    $user->id,
                    25,
                    'feedback',
                    'Shared site testimony and policy feedback'
                );
            } catch (\Throwable $e) {
                // Log or ignore if user_points creation encounters minor issue
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Thank you for your testimony and feedback! (+25 XP & +25 Points earned)',
            'data' => $feedback
        ]);
    }
}
