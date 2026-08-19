<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCenter extends Model
{
    protected $table = 'service_centers';

    protected $fillable = [
        'name',
        'type',
        'custom_type',
        'contact_number',
        'address',
        'description',
        'status',
        'municipality_id',
        'created_by',
    ];

    public function spots()
    {
        return $this->belongsToMany(TouristSpot::class, 'tourist_spot_service_center', 'service_center_id', 'tourist_spot_id');
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
}
