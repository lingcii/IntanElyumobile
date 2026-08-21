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
     * Safely award points to a user, guarding against missing tables or columns.
     */
    public static function awardPointsSafely(int $userId, int $points, string $source, string $description, ?int $spotId = null): ?self
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('user_points')) {
                return null;
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('user_points', 'points')) {
                return null;
            }

            $data = [
                'user_id'     => $userId,
                'points'      => $points,
                'source'      => $source,
                'description' => $description,
            ];

            if ($spotId && \Illuminate\Support\Facades\Schema::hasColumn('user_points', 'spot_id')) {
                $data['spot_id'] = $spotId;
            }

            $up = self::create($data);

            // Also record in activity_logs
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
            } catch (\Throwable $e) {
                // Ignore activity log errors
            }

            return $up;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
