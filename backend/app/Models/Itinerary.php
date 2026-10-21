<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Itinerary extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'trip_date',
        'budget',
        'status',
        'total_cost',
        'route_type',
        'transport_mode',
        'trip_type',
        'companion_males',
        'companion_females',
        'total_pax',
    ];

    protected $casts = [
        'user_id'           => 'integer',
        'trip_date'         => 'date',
        'budget'            => 'decimal:2',
        'total_cost'        => 'decimal:2',
        'companion_males'   => 'integer',
        'companion_females' => 'integer',
        'total_pax'         => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Itinerary $itinerary) {
            ItineraryItem::where('itinerary_id', $itinerary->id)->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItineraryItem::class)->orderBy('order_index', 'asc')->orderBy('id', 'asc');
    }
}
