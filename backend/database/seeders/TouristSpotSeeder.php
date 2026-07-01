<?php

namespace Database\Seeders;

use App\Models\Municipality;
use App\Models\TouristSpot;
use Illuminate\Database\Seeder;

class TouristSpotSeeder extends Seeder
{
    public function run(): void
    {
        $munis = Municipality::all();

        $spots = [
            [
                'name' => 'Tangadan Falls',
                'category' => 'Waterfalls',
                'classification_status' => 'EXIST',
                'entrance_fee' => 50,
                'description' => 'A breathtaking waterfall with clear blue waters perfect for swimming and adventure.',
                'latitude' => 16.35,
                'longitude' => 120.45,
                'opening_time' => '08:00',
                'closing_time' => '17:00',
                'is_maintenance' => false,
                'municipality_id' => $munis->firstWhere('name', 'San Fernando')->id,
            ],
            [
                'name' => 'La Union Surfing Beach',
                'category' => 'Beach',
                'classification_status' => 'EMERGE',
                'entrance_fee' => 0,
                'description' => 'The surfing capital of Northern Luzon with consistent waves year-round.',
                'latitude' => 16.62,
                'longitude' => 120.32,
                'opening_time' => '06:00',
                'closing_time' => '19:00',
                'is_maintenance' => false,
                'municipality_id' => $munis->firstWhere('name', 'San Juan')->id,
            ],
            [
                'name' => 'Poro Point Lighthouse',
                'category' => 'Historical',
                'classification_status' => 'POTENTIAL',
                'entrance_fee' => 30,
                'description' => 'A historic lighthouse with panoramic views of the West Philippine Sea.',
                'latitude' => 16.63,
                'longitude' => 120.30,
                'opening_time' => '07:00',
                'closing_time' => '18:00',
                'is_maintenance' => false,
                'municipality_id' => $munis->firstWhere('name', 'San Fernando')->id,
            ],
            [
                'name' => 'Bauang Beach',
                'category' => 'Beach',
                'classification_status' => 'EXIST',
                'entrance_fee' => 20,
                'description' => 'A peaceful black sand beach perfect for relaxation.',
                'latitude' => 16.53,
                'longitude' => 120.34,
                'opening_time' => '07:00',
                'closing_time' => '18:00',
                'is_maintenance' => false,
                'municipality_id' => $munis->firstWhere('name', 'Bauang')->id,
            ],
        ];

        foreach ($spots as $spot) {
            TouristSpot::create($spot);
        }
    }
}
