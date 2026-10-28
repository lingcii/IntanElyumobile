<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryItem extends Model
{
    protected $fillable = [
        'itinerary_id',
        'tourist_spot_id',
        'order_index',
        'is_visited',
        'companion_males',
        'companion_females',
        'total_pax',
        'proof_image',
        'visited_at',
        'proof_status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'transport_mode',
        'leg_cost',
        'leg_distance_km',
    ];

    protected $casts = [
        'order_index'       => 'integer',
        'is_visited'        => 'boolean',
        'companion_males'   => 'integer',
        'companion_females' => 'integer',
        'total_pax'         => 'integer',
        'visited_at'        => 'datetime',
        'reviewed_at'       => 'datetime',
        'leg_cost'          => 'decimal:2',
        'leg_distance_km'   => 'decimal:2',
    ];

    protected $appends = [
        'proof_images',
        'is_location_verified',
    ];

    /**
     * Format a proof image URL or filename to route through the reliable /api/image streaming endpoint.
     * Prevents clients from failing to resolve *.r2.dev domains due to local ISP DNS restrictions.
     */
    public static function formatProofUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $trimmed = trim($url);
        if (str_starts_with($trimmed, 'data:') || str_starts_with($trimmed, 'blob:')) {
            return $trimmed;
        }

        // Extract proof_xxx filename pattern
        if (preg_match('#proof_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif)#i', $trimmed, $m)) {
            return url('/api/image/proof_images/' . $m[0]);
        }

        return $trimmed;
    }

    /**
     * Get the primary proof image URL string.
     * Backwards-compatible with consumers expecting a single string URL.
     */
    public function getProofImageAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $url = $value;
        if (is_string($value) && str_starts_with(trim($value), '[') && str_ends_with(trim($value), ']')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded) && count($decoded) > 0) {
                $url = $decoded[0];
            }
        }

        return static::formatProofUrl($url);
    }

    /**
     * Get all proof image URLs as an array.
     *
     * @return array<int, string>
     */
    public function getProofImagesAttribute(): array
    {
        $raw = $this->attributes['proof_image'] ?? null;
        if (empty($raw)) {
            return [];
        }

        $list = [$raw];
        if (is_string($raw) && str_starts_with(trim($raw), '[') && str_ends_with(trim($raw), ']')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $list = array_values(array_filter($decoded));
            }
        }

        return array_values(array_filter(array_map([static::class, 'formatProofUrl'], $list)));
    }

    /**
     * Check whether GPS location has been verified.
     */
    public function getIsLocationVerifiedAttribute(): bool
    {
        return $this->visited_at !== null;
    }

    protected static function booted(): void
    {
        static::updated(function (ItineraryItem $item) {
            // If proof_status was updated to approved, sync is_visited = true ONLY IF the tourist has also checked in on location
            if ($item->isDirty('proof_status') && $item->proof_status === 'approved' && !$item->is_visited) {
                if ($item->visited_at !== null) {
                    $item->is_visited = true;
                    $item->saveQuietly();
                }
            }

            // When is_visited transitions to true in the database (requires both check-in and approved proof)
            if (($item->isDirty('is_visited') && $item->is_visited) || ($item->isDirty('proof_status') && $item->proof_status === 'approved' && $item->visited_at !== null)) {
                $item->loadMissing(['itinerary.user', 'destination']);
                $spot = $item->destination;
                $user = $item->itinerary ? $item->itinerary->user : null;

                if ($spot && $user) {
                    // Determine Points & XP based on classification status
                    $rewardPoints = \App\Models\Classification::getPointsForStatus($spot->classification_status);
                    $canonical = \App\Models\Classification::normalizeStatus($spot->classification_status);

                    // Award XP & Level
                    $newXp    = ($user->xp ?? 0) + $rewardPoints;
                    $newLevel = (int) floor($newXp / 1000) + 1;
                    $user->update([
                        'xp' => $newXp,
                        'level' => $newLevel,
                    ]);

                    // Award Points in users table & ledger
                    \App\Models\UserPoint::awardPointsSafely(
                        $user->id,
                        $rewardPoints,
                        'check_in',
                        "GPS Check-in with confirmed photo proof at " . $spot->name,
                        $spot->id
                    );

                    $user->increment('completed_activities');

                    // Clear Caches
                    \Illuminate\Support\Facades\Cache::forget("rank:user:{$user->id}");
                    \Illuminate\Support\Facades\Cache::forget("profile:trips:{$user->id}");

                    // Create Notification for tourist
                    \App\Models\Notification::createSafely(
                        $user->id,
                        'checkin_approved',
                        'Check-in Verified!',
                        "Your photo proof check-in at {$spot->name} was confirmed! Earned +{$rewardPoints} Points & XP ({$canonical})."
                    );
                }
            }
        });
    }

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(TouristSpot::class, 'tourist_spot_id');
    }
}
