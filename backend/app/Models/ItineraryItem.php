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
    ];

    protected $casts = [
        'is_visited' => 'boolean',
        'visited_at' => 'datetime',
    ];

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(TouristSpot::class, 'tourist_spot_id');
    }
}
