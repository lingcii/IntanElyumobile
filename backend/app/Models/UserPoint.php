<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    protected $table = 'user_points';

    protected $fillable = [
        'user_id',
        'points',
        'source',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Safely award points to a user, guarding against missing tables or columns.
     */
    public static function awardPointsSafely(int $userId, int $points, string $source, string $description): ?self
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('user_points')) {
                return null;
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('user_points', 'points')) {
                return null;
            }

            return self::create([
                'user_id'     => $userId,
                'points'      => $points,
                'source'      => $source,
                'description' => $description,
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
