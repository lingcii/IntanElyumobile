<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\ItineraryItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ItineraryItemController extends Controller
{
    private const XP_PER_VISIT = 50;

    /**
     * Maximum allowed distance (in meters) for a valid GPS check-in.
     */
    private const MAX_DISTANCE_METERS = 70;

    private function hasIncompletePreviousStop(ItineraryItem $item): bool
    {
        return ItineraryItem::where('itinerary_id', $item->itinerary_id)
            ->where('order_index', '<', $item->order_index)
            ->where(function ($query) {
                $query->where('is_visited', false)
                    ->orWhereNull('visited_at')
                    ->orWhereNull('proof_status')
                    ->orWhere('proof_status', '!=', 'approved');
            })
            ->exists();
    }

    private function lockedStopResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Complete the previous destination before checking in or uploading proof for this stop.',
        ], 409);
    }

    /**
     * Helper to store multiple proof photos, compressing to WebP and uploading to R2 / local.
     *
     * @param array<\Illuminate\Http\UploadedFile> $files
     * @return array<int, string>
     */
    private function storeProofFiles(array $files): array
    {
        $urls = [];
        $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            try {
                // 1. Always save compressed WebP copy to local folder
                $localPath = \App\Helpers\ImageCompressor::compressAndStore($file, 'proof_images', 'public', 'proof_', 1200, 80);

                // 2. Also upload compressed WebP copy to Cloudflare R2 Bucket
                try {
                    $r2Path = \App\Helpers\ImageCompressor::compressAndStore($file, 'proof_images', 'r2', 'proof_', 1200, 80);
                    $urls[] = $r2PublicUrl . '/' . ltrim($r2Path, '/');
                } catch (\Throwable $r2Exception) {
                    Log::warning("R2 disk store failed for proof, using local fallback: " . $r2Exception->getMessage());
                    $urls[] = 'storage/' . $localPath;
                }
            } catch (\Throwable $imgErr) {
                Log::error("Failed to store proof image: " . $imgErr->getMessage());
            }
        }

        return $urls;
    }

    /**
     * POST /api/tourist/itineraries/items/{id}/checkin
     * Independent Location Check-In process.
     * Verifies tourist is physically within 300m of the tourist spot.
     * Marks location as verified (visited_at) without requiring photos.
     */
    public function checkinLocation(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'lat'         => 'required|numeric|between:-90,90',
                'lng'         => 'required|numeric|between:-180,180',
                'accuracy'    => 'nullable|numeric',
                'altitude'    => 'nullable|numeric',
                'speed'       => 'nullable|numeric',
                'captured_at' => 'nullable|string',
            ]);

            $user = $request->user();

            $item = ItineraryItem::whereHas('itinerary', fn($q) => $q->where('user_id', $user->id))
                ->with('destination:id,name,latitude,longitude,classification_status')
                ->find($id);

            if (!$item) {
                return response()->json(['message' => 'Itinerary item not found or unauthorized.'], 404);
            }

            if ($this->hasIncompletePreviousStop($item)) {
                return $this->lockedStopResponse();
            }

            $spot = $item->destination;
            if (!$spot || !$spot->latitude || !$spot->longitude) {
                return response()->json(['message' => 'This destination has no GPS coordinates set.'], 422);
            }

            $capturedAt = null;
            if ($request->filled('captured_at')) {
                try {
                    $parsed = Carbon::parse($request->captured_at);
                    if ($parsed->lte(now()->addMinutes(10)) && $parsed->gte(now()->subHours(48))) {
                        $capturedAt = $parsed;
                    }
                } catch (\Throwable $e) { }
            }

            $effectiveTime = $capturedAt ?? now();

            // Anti-Spoofing: Accuracy Check
            if ($request->has('accuracy') && $request->accuracy > 200) {
                return response()->json([
                    'message' => 'GPS accuracy is too low (> ' . round($request->accuracy) . 'm). Please move outside for a clearer signal.'
                ], 403);
            }

            // Anti-Spoofing: Teleportation Check (Max 200 km/h) against last GPS ping
            if (isset($user->last_gps_ping_at, $user->last_gps_lat, $user->last_gps_lng)) {
                $lastPing = Carbon::parse($user->last_gps_ping_at);
                $timeDiff = abs($effectiveTime->diffInSeconds($lastPing));

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
                            'message' => 'Suspicious location jump detected. Teleportation is not permitted! 🚫'
                        ], 403);
                    }
                }
            }

            // Haversine distance
            $distanceMeters = $this->haversine(
                (float) $request->lat,
                (float) $request->lng,
                (float) $spot->latitude,
                (float) $spot->longitude
            );

            if ($distanceMeters > self::MAX_DISTANCE_METERS) {
                return response()->json([
                    'message'  => "You're too far from {$spot->name}. Get within " . self::MAX_DISTANCE_METERS . "m to check in! 📍",
                    'distance' => round($distanceMeters) . 'm away',
                    'required' => self::MAX_DISTANCE_METERS . 'm',
                ], 403);
            }

            // Record location verification with companion headcount
            $itin = $item->itinerary;
            $males = $request->has('companion_males') ? max(0, (int)$request->companion_males) : (int)($item->companion_males ?? $itin?->companion_males ?? 0);
            $females = $request->has('companion_females') ? max(0, (int)$request->companion_females) : (int)($item->companion_females ?? $itin?->companion_females ?? 0);
            $totalPax = $request->has('total_pax') ? max(1, (int)$request->total_pax) : max(1, (int)($item->total_pax ?? $itin?->total_pax ?? (1 + $males + $females)));

            $isFullyVisited = ($item->proof_status === 'approved');
            $item->update([
                'visited_at'           => $effectiveTime,
                'is_visited'           => $isFullyVisited,
                'is_location_verified' => true,
                'companion_males'      => $males,
                'companion_females'    => $females,
                'total_pax'            => $totalPax,
            ]);

            try {
                $shouldUpdatePing = !$user->last_gps_ping_at || ($effectiveTime->gte(Carbon::parse($user->last_gps_ping_at)));
                if ($shouldUpdatePing) {
                    $user->update([
                        'last_gps_lat'     => $request->lat,
                        'last_gps_lng'     => $request->lng,
                        'last_gps_ping_at' => $effectiveTime,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning("Could not update user GPS ping: " . $e->getMessage());
            }

            return response()->json([
                'success'              => true,
                'is_location_verified' => true,
                'visited_at'           => $effectiveTime->toIso8601String(),
                'distance'             => round($distanceMeters) . 'm',
                'message'              => "Location verified at {$spot->name}! 📍 Photo proof is required to earn points.",
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation error: ' . implode(' ', \Illuminate\Support\Arr::flatten($ve->errors())),
                'errors'  => $ve->errors(),
            ], 422);
        } catch (\Throwable $ex) {
            Log::error("Check-in error on item {$id}: " . $ex->getMessage());
            return response()->json([
                'message' => 'Unable to verify location: ' . $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/tourist/itineraries/items/{id}/proof
     * Independent Photo Proof Upload process.
     * Supports multiple photo selection (up to 5 photos).
     * Sets proof_status to 'pending' without requiring immediate GPS check-in.
     */
    public function uploadProof(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $item = ItineraryItem::whereHas('itinerary', fn($q) => $q->where('user_id', $user->id))
                ->with('destination:id,name')
                ->find($id);

            if (!$item) {
                return response()->json(['message' => 'Itinerary item not found or unauthorized.'], 404);
            }


            if ($this->hasIncompletePreviousStop($item)) {
                return $this->lockedStopResponse();
            }

            // Gather all uploaded files (supports 'images[]', 'images', or single 'image')
            $files = [];
            if ($request->hasFile('images')) {
                $uploaded = $request->file('images');
                $files = is_array($uploaded) ? $uploaded : [$uploaded];
            } elseif ($request->hasFile('image')) {
                $files = [$request->file('image')];
            }

            if (empty($files)) {
                return response()->json([
                    'message' => 'Please select at least one photo proof to upload.',
                    'errors'  => ['images' => ['No photos were uploaded.']]
                ], 422);
            }

            // Limit to max 5 photos
            $files = array_slice($files, 0, 5);

            $storedUrls = $this->storeProofFiles($files);

            if (empty($storedUrls)) {
                return response()->json([
                    'message' => 'Could not process uploaded photos. Please try different photos.',
                ], 422);
            }

            // Store JSON array of image URLs
            $jsonImages = json_encode($storedUrls);

            $item->update([
                'proof_image'      => $jsonImages,
                'proof_status'     => 'pending',
                'rejection_reason' => null,
            ]);

            return response()->json([
                'success'      => true,
                'proof_status' => 'pending',
                'proof_image'  => $storedUrls[0],
                'proof_images' => $storedUrls,
                'message'      => 'Photo proof submitted! Awaiting review before completion.',
            ]);
        } catch (\Throwable $ex) {
            Log::error("Proof upload error on item {$id}: " . $ex->getMessage());
            return response()->json([
                'message' => 'Failed to upload photo proof: ' . $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * PATCH/POST /api/tourist/itineraries/items/{id}/visit
     * Combined GPS check-in and multi-photo proof upload.
     */
    public function visit(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'lat'         => 'required|numeric|between:-90,90',
                'lng'         => 'required|numeric|between:-180,180',
                'accuracy'    => 'nullable|numeric',
                'altitude'    => 'nullable|numeric',
                'speed'       => 'nullable|numeric',
                'captured_at' => 'nullable|string',
                'image'       => 'nullable|file|max:20480',
                'images'      => 'nullable|array',
                'images.*'    => 'file|max:20480',
            ]);

            $user = $request->user();

            $item = ItineraryItem::whereHas('itinerary', fn($q) => $q->where('user_id', $user->id))
                ->with('destination:id,name,latitude,longitude,classification_status')
                ->find($id);

            if (!$item) {
                return response()->json(['message' => 'Itinerary item not found or unauthorized.'], 404);
            }


            if ($this->hasIncompletePreviousStop($item)) {
                return $this->lockedStopResponse();
            }

            $hasIncomingPhotos = $request->hasFile('images') || $request->hasFile('image');

            // Graceful idempotency for retried offline syncs (only if proof already uploaded and no new photos provided)
            if (($item->is_visited && !empty($item->proof_image) && !$hasIncomingPhotos) || ($item->proof_status === 'approved' && !empty($item->proof_image) && !$hasIncomingPhotos)) {
                return response()->json([
                    'success'          => true,
                    'already_recorded' => true,
                    'status'           => $item->proof_status ?? ($item->is_visited ? 'approved' : 'pending'),
                    'message'          => 'Check-in proof already recorded for this destination.',
                ], 200);
            }

            $spot = $item->destination;

            if (!$spot || !$spot->latitude || !$spot->longitude) {
                return response()->json(['message' => 'This destination has no GPS coordinates set.'], 422);
            }

            $capturedAt = null;
            if ($request->filled('captured_at')) {
                try {
                    $parsed = Carbon::parse($request->captured_at);
                    if ($parsed->lte(now()->addMinutes(10)) && $parsed->gte(now()->subHours(48))) {
                        $capturedAt = $parsed;
                    }
                } catch (\Throwable $e) { }
            }

            $effectiveTime = $capturedAt ?? now();

            // Anti-Spoofing: Accuracy Check
            if ($request->has('accuracy') && $request->accuracy > 200 && !$hasIncomingPhotos) {
                return response()->json([
                    'message' => 'GPS accuracy is too low (> ' . round($request->accuracy) . 'm). Please go outside and wait for a better signal.'
                ], 403);
            }

            // Anti-Spoofing: Teleportation Check (Max 200 km/h) against last GPS ping
            if (isset($user->last_gps_ping_at, $user->last_gps_lat, $user->last_gps_lng) && !$request->filled('captured_at')) {
                $lastPing = Carbon::parse($user->last_gps_ping_at);
                $timeDiff = abs($effectiveTime->diffInSeconds($lastPing));
                
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
                    'message'  => "You're too far from {$spot->name}. Get within " . self::MAX_DISTANCE_METERS . "m to check in! 📍",
                    'distance' => round($distanceMeters) . 'm away',
                    'required' => self::MAX_DISTANCE_METERS . 'm',
                ], 403);
            }

            $isFullyVisited = ($item->proof_status === 'approved');
            $itemData = [
                'is_visited'           => $isFullyVisited,
                'visited_at'           => $effectiveTime,
                'is_location_verified' => true,
            ];

            // Collect files
            $files = [];
            if ($request->hasFile('images')) {
                $uploaded = $request->file('images');
                $files = is_array($uploaded) ? $uploaded : [$uploaded];
            } elseif ($request->hasFile('image')) {
                $files = [$request->file('image')];
            }

            if (!empty($files)) {
                $files = array_slice($files, 0, 5);
                $storedUrls = $this->storeProofFiles($files);
                if (!empty($storedUrls)) {
                    $itemData['proof_image']  = json_encode($storedUrls);
                    $itemData['proof_status'] = 'pending';
                }
            }

            $itin = $item->itinerary;
            $males = $request->has('companion_males') ? max(0, (int)$request->companion_males) : (int)($item->companion_males ?? $itin?->companion_males ?? 0);
            $females = $request->has('companion_females') ? max(0, (int)$request->companion_females) : (int)($item->companion_females ?? $itin?->companion_females ?? 0);
            $totalPax = $request->has('total_pax') ? max(1, (int)$request->total_pax) : max(1, (int)($item->total_pax ?? $itin?->total_pax ?? (1 + $males + $females)));

            $itemData['companion_males']   = $males;
            $itemData['companion_females'] = $females;
            $itemData['total_pax']         = $totalPax;

            $item->update($itemData);
            $item->refresh();

            try {
                $shouldUpdatePing = !$user->last_gps_ping_at || ($effectiveTime->gte(Carbon::parse($user->last_gps_ping_at)));
                if ($shouldUpdatePing) {
                    $user->update([
                        'last_gps_lat'     => $request->lat,
                        'last_gps_lng'     => $request->lng,
                        'last_gps_ping_at' => $effectiveTime,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning("Could not update user GPS ping: " . $e->getMessage());
            }

            return response()->json([
                'success'              => true,
                'status'               => $itemData['proof_status'] ?? 'verified',
                'is_location_verified' => true,
                'visited_at'           => $effectiveTime->toIso8601String(),
                'message'              => !empty($itemData['proof_image']) ? "Photo proof submitted! Pending verification before completion." : "GPS Check-in verified! Photo proof required to earn points.",
                'proof_image'          => $item->proof_image,
                'proof_images'         => $item->proof_images,
                'distance'             => round($distanceMeters) . 'm',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation error: ' . implode(' ', \Illuminate\Support\Arr::flatten($ve->errors())),
                'errors'  => $ve->errors(),
            ], 422);
        } catch (\Throwable $ex) {
            Log::error("Check-in error on item {$id}: " . $ex->getMessage() . "\n" . $ex->getTraceAsString());
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
