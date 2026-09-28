<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FareMatrix extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'fare_guide_id', 'distance_km', 'regular_fare', 'discounted_fare'
    ];
    
    public function guide()
    {
        return $this->belongsTo(FareGuide::class, 'fare_guide_id');
    }

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('map:public:fares:v5');
            \Illuminate\Support\Facades\Cache::forget('map:public:fares');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('map:public:fares:v5');
            \Illuminate\Support\Facades\Cache::forget('map:public:fares');
        });
    }
}
