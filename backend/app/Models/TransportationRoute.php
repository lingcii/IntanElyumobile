<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportationRoute extends Model
{
    protected $fillable = [
        'destination', 'tourist_spot_id', 'fare_matrices_id', 'vehicle_type', 'vehicle_id'
    ];

    public function touristSpot()
    {
        return $this->belongsTo(TouristSpot::class);
    }

    public function fareMatrix()
    {
        return $this->belongsTo(FareMatrix::class, 'fare_matrices_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
