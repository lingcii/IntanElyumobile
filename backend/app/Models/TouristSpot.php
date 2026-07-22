<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TouristSpot extends Model
{
    protected $table = 'tourist_spots';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'municipality_id',
        'barangay',
        'category',
        'entrance_fee',
        'description',
        'photo_url',
        'latitude',
        'longitude',
        'opening_time',
        'closing_time',
        'is_maintenance',
        'status',
        'classification_status',
        'visits',
        'rating',
    ];

    protected $casts = [
        'entrance_fee'   => 'float',
        'latitude'       => 'float',
        'longitude'      => 'float',
        'is_maintenance' => 'boolean',
        'visits'         => 'integer',
        'rating'         => 'float',
    ];

    public static array $VALID_CATEGORIES = [
        'Beach', 'Mountain', 'Waterfalls', 'River', 'Lake', 'Island',
        'Cave', 'Volcano', 'Forest', 'Nature Park', 'Marine Sanctuary',
        'Wildlife Sanctuary', 'Historical', 'Cultural Heritage', 'Religious',
        'Museum', 'Monument', 'Landmark', 'Viewpoint', 'Adventure', 'Hiking',
        'Camping', 'Farm', 'Eco-Tourism', 'Garden', 'Park', 'Recreation',
        'Hot Spring', 'Cold Spring', 'Food Destination', 'Shopping',
        'Festival Venue', 'Resort', 'Other'
    ];

    public static array $VALID_STATUSES = ['EXIST', 'POTENTIAL', 'EMERGE'];

    public static array $STATUS_MAP = [
        'EXISTING'  => 'EXIST',
        'EMERGING'  => 'EMERGE',
        'POTENTIAL' => 'POTENTIAL',
        'EXIST'     => 'EXIST',
        'EMERGE'    => 'EMERGE',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function images()
    {
        return $this->hasMany(TouristSpotImage::class, 'spot_id')->orderBy('sort_order')->orderBy('id');
    }

    public function audits()
    {
        return $this->hasMany(TouristSpotAudit::class, 'spot_id');
    }
}
