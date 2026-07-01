<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TouristSpotImage extends Model
{
    protected $table = 'tourist_spot_images';

    public $timestamps = false;

    protected $fillable = [
        'spot_id',
        'photo_url',
        'is_primary',
        'sort_order',
    ];

    public function spot()
    {
        return $this->belongsTo(TouristSpot::class, 'spot_id');
    }
}
