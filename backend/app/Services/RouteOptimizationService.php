<?php

namespace App\Services;

use App\Models\TouristSpot;
use Illuminate\Support\Facades\Log;

class RouteOptimizationService
{
    /**
     * Solve Travelling Salesperson Problem (TSP) with Time Windows and Traffic factor.
     * Returns the optimized sequence of TouristSpot IDs.
     */
    public function optimizeRoute(array $spotIds, ?float $startLat = null, ?float $startLng = null): array
    {
        if (count($spotIds) <= 1) {
            return $spotIds;
        }

        // Fetch spots with coordinates and hours
        $spots = TouristSpot::whereIn('id', $spotIds)->get();

        if ($spots->isEmpty()) {
            return $spotIds;
        }

        $unvisited = $spots->keyBy('id');
        $optimizedPath = [];
        
        // Starting location
        $currentLat = $startLat;
        $currentLng = $startLng;

        // If no start coordinates provided, start at the first spot in the user's original selection
        if ($currentLat === null || $currentLng === null) {
            $firstId = $spotIds[0];
            $firstSpot = $unvisited->get($firstId);
            if ($firstSpot) {
                $optimizedPath[] = $firstSpot->id;
                $currentLat = (float) $firstSpot->latitude;
                $currentLng = (float) $firstSpot->longitude;
                $unvisited->forget($firstSpot->id);
            } else {
                $firstSpot = $unvisited->first();
                $optimizedPath[] = $firstSpot->id;
                $currentLat = (float) $firstSpot->latitude;
                $currentLng = (float) $firstSpot->longitude;
                $unvisited->forget($firstSpot->id);
            }
        }

        // Current time: Start the trip at 8:00 AM (480 minutes since midnight)
        $currentTimeMinutes = 480; 
        $visitDurationMinutes = 90; // Default average visit time of 1.5 hours per spot

        while ($unvisited->isNotEmpty()) {
            $bestCandidate = null;
            $lowestScore = INF;
            $bestTravelTime = 0;

            foreach ($unvisited as $spot) {
                $lat = (float) $spot->latitude;
                $lng = (float) $spot->longitude;

                // 1. Calculate proximity (Haversine distance in km)
                $distance = $this->haversine($currentLat, $currentLng, $lat, $lng) / 1000.0;

                // 2. Estimate travel time (assume average speed of 40 km/h)
                $travelTimeMinutes = ($distance / 40.0) * 60.0;

                // 3. Traffic Buffer penalty: Apply rush hour multiplier (07:00-09:00 or 16:00-19:00)
                $arrivalHour = floor(($currentTimeMinutes + $travelTimeMinutes) / 60) % 24;
                $isRushHour = ($arrivalHour >= 7 && $arrivalHour <= 9) || ($arrivalHour >= 16 && $arrivalHour <= 19);
                if ($isRushHour) {
                    $travelTimeMinutes *= 1.4; // 40% travel delay during rush hours
                }

                $arrivalTime = $currentTimeMinutes + $travelTimeMinutes;
                $departureTime = $arrivalTime + $visitDurationMinutes;

                // 4. Operating hours alignment penalty
                $timeWindowPenalty = 0.0;
                if ($spot->opening_time && $spot->closing_time) {
                    $openMin = $this->timeStringToMinutes($spot->opening_time);
                    $closeMin = $this->timeStringToMinutes($spot->closing_time);

                    // If we arrive before opening, we must wait (increases cost)
                    if ($arrivalTime < $openMin) {
                        $timeWindowPenalty += ($openMin - $arrivalTime) * 0.5; // wait penalty
                    }
                    // If we depart after closing, we apply a huge penalty (closed)
                    if ($departureTime > $closeMin) {
                        $timeWindowPenalty += 1000.0; // Closed penalty
                    }
                }

                // Total score (lower is better): Distance + time window deviations
                $score = $distance + ($timeWindowPenalty / 10.0); // Scale time penalty

                if ($score < $lowestScore) {
                    $lowestScore = $score;
                    $bestCandidate = $spot;
                    $bestTravelTime = $travelTimeMinutes;
                }
            }

            if ($bestCandidate) {
                $optimizedPath[] = $bestCandidate->id;
                $currentLat = (float) $bestCandidate->latitude;
                $currentLng = (float) $bestCandidate->longitude;
                
                // Advance clock by travel time + visit duration
                $currentTimeMinutes += $bestTravelTime + $visitDurationMinutes;
                
                $unvisited->forget($bestCandidate->id);
            } else {
                break;
            }
        }

        // Add any leftovers just in case
        foreach ($unvisited as $spot) {
            $optimizedPath[] = $spot->id;
        }

        return $optimizedPath;
    }

    /**
     * Haversine distance in meters.
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Convert "HH:MM:SS" or "HH:MM" format to minutes since midnight.
     */
    private function timeStringToMinutes(string $timeStr): int
    {
        $parts = explode(':', $timeStr);
        $hours = isset($parts[0]) ? (int) $parts[0] : 0;
        $minutes = isset($parts[1]) ? (int) $parts[1] : 0;
        return ($hours * 60) + $minutes;
    }
}
