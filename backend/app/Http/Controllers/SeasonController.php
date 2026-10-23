<?php

namespace App\Http\Controllers;

use App\Models\TourismSeason;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    /**
     * GET /api/seasons/status
     * Returns current or specified date peak season status, active surcharges, and travel advisory.
     */
    public function getStatus(Request $request): JsonResponse
    {
        try {
            $dateString = $request->query('date');
            $municipality = $request->query('municipality');

            try {
                $targetDate = $dateString ? Carbon::parse($dateString) : now();
            } catch (\Throwable $e) {
                $targetDate = now();
            }

            $activeSeasons = TourismSeason::getActiveSeasonsForDate(
                $targetDate->toDateString(),
                $municipality
            );

            $isPeak = $activeSeasons->isNotEmpty();

            $maxSurcharge = $isPeak ? (float) $activeSeasons->max('fare_surcharge_percent') : 0.0;
            $maxCostMultiplier = $isPeak ? (float) $activeSeasons->max('cost_multiplier') : 1.0;
            $maxTrafficDelay = $isPeak ? (float) $activeSeasons->max('traffic_delay_multiplier') : 1.0;

            // Generate contextual tourist advisory
            if ($isPeak) {
                $seasonNames = $activeSeasons->pluck('name')->implode(', ');
                $surchargeDisplay = '+' . round($maxSurcharge) . '%';
                $notice = "☀️ Peak Season Active ({$seasonNames}): Standard LGU {$surchargeDisplay} fare surge applied. Expect heavier traffic on coastal routes.";
                $crowdLevel = 'high';
            } else {
                $notice = "🌿 Regular Tourism Season: Standard regular fares and normal traffic flow.";
                $crowdLevel = 'moderate';
            }

            return response()->json([
                'status'  => 'success',
                'success' => true,
                'data'    => [
                    'date'                              => $targetDate->toDateString(),
                    'is_peak'                           => $isPeak,
                    'is_peak_season'                    => $isPeak,
                    'effective_surcharge_percent'       => $maxSurcharge,
                    'fare_multiplier'                   => round(1 + ($maxSurcharge / 100), 2),
                    'effective_cost_multiplier'         => $maxCostMultiplier,
                    'effective_traffic_delay_multiplier'=> $maxTrafficDelay,
                    'crowd_level'                       => $crowdLevel,
                    'notice'                            => $notice,
                    'active_seasons'                    => $activeSeasons->map(function ($s) {
                        return [
                            'id'                       => $s->id,
                            'name'                     => $s->name,
                            'slug'                     => $s->slug,
                            'type'                     => $s->type,
                            'municipality'             => $s->municipality,
                            'fare_surcharge_percent'   => $s->fare_surcharge_percent,
                            'cost_multiplier'          => $s->cost_multiplier,
                            'traffic_delay_multiplier' => $s->traffic_delay_multiplier,
                            'description'              => $s->description,
                        ];
                    }),
                ],
            ]);
        } catch (\Throwable $e) {
            // Graceful fallback if database error
            $currentMonth = (int) now()->format('n');
            $fallbackIsPeak = in_array($currentMonth, [1, 3, 4, 5, 10, 11, 12], true) || now()->isWeekend();

            return response()->json([
                'status'  => 'success',
                'success' => true,
                'data'    => [
                    'date'                              => now()->toDateString(),
                    'is_peak'                           => $fallbackIsPeak,
                    'is_peak_season'                    => $fallbackIsPeak,
                    'effective_surcharge_percent'       => $fallbackIsPeak ? 20.0 : 0.0,
                    'fare_multiplier'                   => $fallbackIsPeak ? 1.20 : 1.00,
                    'effective_cost_multiplier'         => $fallbackIsPeak ? 1.20 : 1.00,
                    'effective_traffic_delay_multiplier'=> $fallbackIsPeak ? 1.25 : 1.00,
                    'crowd_level'                       => $fallbackIsPeak ? 'high' : 'moderate',
                    'notice'                            => $fallbackIsPeak
                        ? "☀️ Peak Season Active: Standard +20% fare surcharge included."
                        : "🌿 Regular Season: Standard fares active.",
                    'active_seasons'                    => [],
                ],
            ]);
        }
    }

    /**
     * GET /api/seasons
     * Returns all configured tourism seasons.
     */
    public function index(): JsonResponse
    {
        try {
            $seasons = TourismSeason::orderBy('start_month', 'asc')->get();
            return response()->json([
                'status'  => 'success',
                'success' => true,
                'seasons' => $seasons,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve seasons.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
