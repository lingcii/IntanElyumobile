<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Classification extends Model
{
    protected $table = 'classifications';

    protected $fillable = [
        'name',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    /**
     * Normalize status string to canonical classification name (EXISTING, EMERGING, POTENTIAL).
     */
    public static function normalizeStatus(?string $status): string
    {
        $status = strtoupper(trim((string) $status));
        if (str_starts_with($status, 'EMERG')) {
            return 'EMERGING';
        }
        if (str_starts_with($status, 'POT')) {
            return 'POTENTIAL';
        }
        return 'EXISTING';
    }

    /**
     * Get points for a given classification status, querying the database with caching and safe fallbacks.
     */
    public static function getPointsForStatus(?string $status): int
    {
        $canonical = self::normalizeStatus($status);

        return (int) Cache::remember("classification_points:{$canonical}", 3600, function () use ($canonical) {
            try {
                $row = self::where('name', $canonical)->first();
                if ($row && $row->points > 0) {
                    return (int) $row->points;
                }
            } catch (\Throwable $e) {
                // Ignore error and fall through to default match
            }

            return match ($canonical) {
                'EMERGING'  => 100,
                'POTENTIAL' => 75,
                default     => 50,
            };
        });
    }

    /**
     * Invalidate cached points for all classifications.
     */
    public static function clearCache(): void
    {
        Cache::forget('classification_points:EXISTING');
        Cache::forget('classification_points:EMERGING');
        Cache::forget('classification_points:POTENTIAL');
    }

    protected static function booted(): void
    {
        static::saved(function () {
            self::clearCache();
        });
        static::deleted(function () {
            self::clearCache();
        });
    }
}
