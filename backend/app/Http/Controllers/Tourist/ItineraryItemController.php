<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\ItineraryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ItineraryItemController extends Controller
{
    private const XP_PER_VISIT = 50;

    /**
     * Maximum allowed distance (in meters) for a valid GPS check-in.
     */
    private const MAX_DISTANCE_METERS = 300;

    /**
     * PATCH /api/tourist/itineraries/items/{id}/visit
     * Awards XP only if the user's GPS position is within MAX_DISTANCE_METERS
     * of the tourist spot's coordinates.
     */
    public function visit(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
            'altitude' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $user = $request->user();

        $item = ItineraryItem::whereHas('itinerary', fn($q) => $q->where('user_id', $user->id))
            ->with('destination:id,name,latitude,longitude,classification_status')
            ->findOrFail($id);

        if ($item->is_visited) {
            return response()->json(['message' => 'You have already checked in at this spot.'], 409);
        }

        $spot = $item->destination;

        if (!$spot || !$spot->latitude || !$spot->longitude) {
            return response()->json(['message' => 'This destination has no GPS coordinates set.'], 422);
        }

        // Anti-Spoofing: Accuracy Check
        if ($request->has('accuracy') && $request->accuracy > 200) {
            return response()->json([
                'message' => 'GPS accuracy is too low (> ' . round($request->accuracy) . 'm). Please go outside and wait for a better signal.'
            ], 403);
        }

        // Anti-Spoofing: Teleportation Check (Max 200 km/h)
        if ($user->last_gps_ping_at && $user->last_gps_lat && $user->last_gps_lng) {
            $timeDiff = now()->diffInSeconds($user->last_gps_ping_at);
            
            // Only check if time difference is > 0 and less than 24 hours
            if ($timeDiff > 0 && $timeDiff < 86400) {
                $distFromLast = $this->haversine(
                     (float) $request->lat,
                     (float) $request->lng,
                     (float) $user->last_gps_lat,
                     (float) $user->last_gps_lng
                );
                
                $speedKmh = ($distFromLast / $timeDiff) * 3.6; // Convert m/s to km/h
                
                if ($speedKmh > 200) {
                    return response()->json([
                        'message' => 'Suspicious location change detected. Teleportation is not allowed! 🚫'
                    ], 403);
                }
            }
        }

        // Haversine formula — great-circle distance between two GPS points
        $distanceMeters = $this->haversine(
            (float) $request->lat,
            (float) $request->lng,
            (float) $spot->latitude,
            (float) $spot->longitude
        );

        if ($distanceMeters > self::MAX_DISTANCE_METERS) {
            return response()->json([
                'message'  => "You're too far from {$spot->name}. Get closer to check in! 📍",
                'distance' => round($distanceMeters) . 'm away',
                'required' => self::MAX_DISTANCE_METERS . 'm',
            ], 403);
        }

        // GPS confirmed — mark as visited
        $itemData = [
            'is_visited' => true,
            'visited_at' => now(),
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('proofs', 'public');
            $itemData['proof_image'] = 'storage/' . $path;
        }

        $item->update($itemData);

        // Increment the tourist spot's visit counter
        $item->destination()->increment('visits');

        // Determine XP based on classification status
        $baseXp = self::XP_PER_VISIT; // 50
        $xpEarned = match($spot->classification_status) {
            'EMERGE'    => 100,
            'POTENTIAL' => 75,
            default     => $baseXp,
        };

        // Award XP
        $newXp    = ($user->xp ?? 0) + $xpEarned;
        $newLevel = (int) floor($newXp / 1000) + 1;
        $user->update([
            'xp' => $newXp, 
            'level' => $newLevel,
            'last_gps_lat' => $request->lat,
            'last_gps_lng' => $request->lng,
            'last_gps_ping_at' => now(),
        ]);

        // Award Points in points ledger
        \App\Models\UserPoint::create([
            'user_id' => $user->id,
            'points' => 50,
            'source' => 'check_in',
            'description' => "GPS Check-in with photo proof at " . $spot->name,
        ]);

        // Technique 5: Denormalization — increment the counter on users table
        $user->increment('completed_activities');

        // Technique 2: Cache Invalidation — flush stale rank/profile caches
        Cache::forget("rank:user:{$user->id}");
        Cache::forget("profile:trips:{$user->id}");

        $bonusMsg = $xpEarned > $baseXp ? "🔥 (Bonus for discovering new spots!)" : "🌟";

        return response()->json([
            'message'   => "You're here! +{$xpEarned} XP earned! {$bonusMsg}",
            'xp_earned' => $xpEarned,
            'total_xp'  => $newXp,
            'new_level' => $newLevel,
            'distance'  => round($distanceMeters) . 'm',
        ]);
    }

    /**
     * Haversine formula — returns the distance in meters between two lat/lng points.
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // metres

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
