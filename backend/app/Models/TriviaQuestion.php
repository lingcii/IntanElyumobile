<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TriviaQuestion extends Model
{
    protected $table = 'trivia_questions';

    protected $fillable = [
        'spot_id',
        'question',
        'options',
        'correct_index',
        'difficulty',
        'fun_fact',
        'is_active',
    ];

    protected $casts = [
        'options'       => 'array',
        'correct_index' => 'integer',
        'is_active'     => 'boolean',
    ];

    public function spot()
    {
        return $this->belongsTo(TouristSpot::class, 'spot_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Get a random active question for a given spot (falls back to general) */
    public static function randomForSpot(?int $spotId): ?self
    {
        if ($spotId) {
            $q = static::active()->where('spot_id', $spotId)->inRandomOrder()->first();
            if ($q) return $q;
        }
        // Fall back to general La Union trivia (spot_id is null)
        return static::active()->whereNull('spot_id')->inRandomOrder()->first();
    }
}
