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
        try {
            $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'accuracy' => 'nullable|numeric',
                'altitude' => 'nullable|numeric',
                'speed' => 'nullable|numeric',
                'image' => 'nullable|file|max:10240',
            ]);

            $user = $request->user();

            $item = ItineraryItem::whereHas('itinerary', fn($q) => $q->where('user_id', $user->id))
                ->with('destination:id,name,latitude,longitude,classification_status')
                ->find($id);

            if (!$item) {
                return response()->json(['message' => 'Itinerary item not found or unauthorized.'], 404);
            }

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
            if (isset($user->last_gps_ping_at, $user->last_gps_lat, $user->last_gps_lng)) {
                $timeDiff = now()->diffInSeconds($user->last_gps_ping_at);
                
                if ($timeDiff > 0 && $timeDiff < 86400) {
                    $distFromLast = $this->haversine(
                         (float) $request->lat,
                         (float) $request->lng,
                         (float) $user->last_gps_lat,
                         (float) $user->last_gps_lng
                    );
                    
                    $speedKmh = ($distFromLast / $timeDiff) * 3.6;
                    
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

            $itemData = [
                'is_visited'   => false,
                'proof_status' => 'pending',
            ];

            if ($request->hasFile('image')) {
                try {
                    $file = $request->file('image');
                    $ext = $file->getClientOriginalExtension() ?: 'jpg';
                    $filename = 'proof_' . random_int(10000000, 99999999) . '.' . $ext;
                    $disk = env('FILESYSTEM_DISK', 'public');

                    try {
                        $path = $file->storeAs('proof_images', $filename, $disk);
                    } catch (\Throwable $diskException) {
                        \Illuminate\Support\Facades\Log::warning("Primary disk {$disk} failed, falling back to public disk: " . $diskException->getMessage());
                        $disk = 'public';
                        $path = $file->storeAs('proof_images', $filename, 'public');
                    }

                    if (in_array($disk, ['r2', 's3'])) {
                        $itemData['proof_image'] = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);
                    } else {
                        $itemData['proof_image'] = 'storage/' . $path;
                    }
                } catch (\Throwable $imgErr) {
                    \Illuminate\Support\Facades\Log::error("Failed to store proof image: " . $imgErr->getMessage());
                }
            }

            $item->update($itemData);

            try {
                $user->update([
                    'last_gps_lat'     => $request->lat,
                    'last_gps_lng'     => $request->lng,
                    'last_gps_ping_at' => now(),
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Could not update user GPS ping: " . $e->getMessage());
            }

            return response()->json([
                'status'      => 'pending',
                'message'     => "Photo proof saved in database! 📸 Pending admin confirmation before completion.",
                'proof_image' => $itemData['proof_image'] ?? null,
                'distance'    => round($distanceMeters) . 'm',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation error: ' . implode(' ', \Illuminate\Support\Arr::flatten($ve->errors())),
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Throwable $ex) {
            \Illuminate\Support\Facades\Log::error("Check-in error on item {$id}: " . $ex->getMessage() . "\n" . $ex->getTraceAsString());
            return response()->json([
                'message' => 'Unable to complete check-in: ' . $ex->getMessage(),
            ], 500);
        }
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
