<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryItem extends Model
{
    protected $fillable = [
        'itinerary_id',
        'tourist_spot_id',
        'is_visited',
        'proof_image',
        'visited_at',
        'proof_status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'is_visited'  => 'boolean',
        'visited_at'   => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updated(function (ItineraryItem $item) {
            // If proof_status was updated to approved, sync is_visited = true
            if ($item->isDirty('proof_status') && $item->proof_status === 'approved' && !$item->is_visited) {
                $item->is_visited = true;
                $item->visited_at = $item->visited_at ?? now();
                $item->saveQuietly();
            }

            // When is_visited transitions to true in the database (or proof_status is approved)
            if (($item->isDirty('is_visited') && $item->is_visited) || ($item->isDirty('proof_status') && $item->proof_status === 'approved')) {
                $item->loadMissing(['itinerary.user', 'destination']);
                $spot = $item->destination;
                $user = $item->itinerary ? $item->itinerary->user : null;

                if ($spot && $user) {
                    // Increment spot visit count
                    $spot->increment('visits');

                    // Determine XP based on classification status
                    $baseXp = 50;
                    $xpEarned = match($spot->classification_status) {
                        'EMERGE'    => 100,
                        'POTENTIAL' => 75,
                        default     => $baseXp,
                    };

                    // Award XP & Level
                    $newXp    = ($user->xp ?? 0) + $xpEarned;
                    $newLevel = (int) floor($newXp / 1000) + 1;
                    $user->update([
                        'xp' => $newXp,
                        'level' => $newLevel,
                    ]);

                    // Award Points in points ledger
                    \App\Models\UserPoint::awardPointsSafely(
                        $user->id,
                        50,
                        'check_in',
                        "GPS Check-in with confirmed photo proof at " . $spot->name
                    );

                    $user->increment('completed_activities');

                    // Clear Caches
                    \Illuminate\Support\Facades\Cache::forget("rank:user:{$user->id}");
                    \Illuminate\Support\Facades\Cache::forget("profile:trips:{$user->id}");

                    // Create Notification for tourist
                    \App\Models\Notification::createSafely(
                        $user->id,
                        'checkin_approved',
                        'Check-in Verified! 🎉',
                        "Your photo proof check-in at {$spot->name} was confirmed by admin! Earned +{$xpEarned} XP & +50 Points."
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
