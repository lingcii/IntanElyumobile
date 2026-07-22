<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    public $timestamps = false;
    protected $table = 'municipalities';

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'attraction_count',
    ];

    public function touristSpots()
    {
        return $this->hasMany(TouristSpot::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function analytics()
    {
        return $this->hasMany(Analytics::class);
    }
}
