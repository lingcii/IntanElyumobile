<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    protected $table = 'user_points';

    protected $fillable = [
        'user_id',
        'spot_id',
        'points',
        'source',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function spot()
    {
        return $this->belongsTo(TouristSpot::class, 'spot_id');
    }

    /**
     * Safely award points to a user, incrementing users.points and logging to activity_logs.
     */
    public static function awardPointsSafely(int $userId, int $points, string $source, string $description, ?int $spotId = null): ?self
    {
        try {
            // Increment points on users table
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'points')) {
                    User::where('id', $userId)->increment('points', $points);
                }
            } catch (\Throwable $e) {}

            // Record in activity_logs
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                    $sourceLabel = ucwords(str_replace('_', ' ', $source));
                    \App\Models\ActivityLog::create([
                        'user_id'    => $userId,
                        'action'     => 'Points Awarded',
                        'details'    => "Earned +{$points} Points ({$sourceLabel}): {$description}",
                        'ip_address' => request()->ip() ?? '127.0.0.1',
                    ]);
                }
            } catch (\Throwable $e) {}

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
