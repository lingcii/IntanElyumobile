<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'name', 'description', 'icon', 'is_active'
    ];

    public function fareGuides()
    {
        return $this->hasMany(FareGuide::class);
    }

    public function transportationRoutes()
    {
        return $this->hasMany(TransportationRoute::class);
    }
}
