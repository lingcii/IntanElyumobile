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
        'environmental_fee',
        'fee_types',
        'route_guide',
        'tour_guide_notice',
        'accessible_by_private_vehicle',
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
        'entrance_fee'                  => 'float',
        'environmental_fee'             => 'float',
        'fee_types'                     => 'array',
        'accessible_by_private_vehicle' => 'boolean',
        'latitude'                      => 'float',
        'longitude'                     => 'float',
        'is_maintenance'                => 'boolean',
        'visits'                        => 'integer',
        'rating'                        => 'float',
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

    public function serviceCenters()
    {
        return $this->belongsToMany(ServiceCenter::class, 'tourist_spot_service_center', 'tourist_spot_id', 'service_center_id');
    }

    public function getPhotoUrlAttribute($value)
    {
        if (!$value) {
            $firstImg = $this->relationLoaded('images') ? $this->images->first()?->photo_url : null;
            $value = $firstImg;
        }
        if (!$value) return null;

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:')) {
            return $value;
        }

        // If filename/relative path is for a spot image, resolve directly to Cloudflare R2 public bucket
        $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');
        $clean = ltrim($value, '/');
        if (preg_match('#^tourist_spots/#i', $clean)) {
            return $r2PublicUrl . '/' . $clean;
        }
        if (preg_match('#^spot_#i', $clean)) {
            return $r2PublicUrl . '/tourist_spots/' . $clean;
        }

        return asset($clean);
    }


    public function audits()
    {
        return $this->hasMany(TouristSpotAudit::class, 'spot_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(SiteFeedback::class, 'tourist_spot_id');
    }

    public function userPoints()
    {
        return $this->hasMany(UserPoint::class, 'spot_id');
    }
}
