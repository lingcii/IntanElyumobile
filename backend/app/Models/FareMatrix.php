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
}
