<?php

namespace Database\Seeders;

use App\Models\Municipality;
use Illuminate\Database\Seeder;

class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $municipalities = [
            ['name' => 'San Fernando', 'latitude' => 16.6150, 'longitude' => 120.3170],
            ['name' => 'San Juan', 'latitude' => 16.6664, 'longitude' => 120.3367],
            ['name' => 'Bauang', 'latitude' => 16.5200, 'longitude' => 120.3300],
            ['name' => 'Agoo', 'latitude' => 16.3300, 'longitude' => 120.3600],
            ['name' => 'Luna', 'latitude' => 16.8500, 'longitude' => 120.3800],
            ['name' => 'San Gabriel', 'latitude' => 16.6800, 'longitude' => 120.4200],
            ['name' => 'Balaoan', 'latitude' => 16.8200, 'longitude' => 120.4000],
            ['name' => 'Aringay', 'latitude' => 16.3900, 'longitude' => 120.3700],
            ['name' => 'Rosario', 'latitude' => 16.2300, 'longitude' => 120.4800],
            ['name' => 'Bacnotan', 'latitude' => 16.7200, 'longitude' => 120.3500],
            ['name' => 'Naguilian', 'latitude' => 16.5300, 'longitude' => 120.4300],
            ['name' => 'Tubao', 'latitude' => 16.3500, 'longitude' => 120.4200],
            ['name' => 'Pugo', 'latitude' => 16.3000, 'longitude' => 120.4800],
            ['name' => 'Caba', 'latitude' => 16.4400, 'longitude' => 120.3400],
            ['name' => 'Santo Tomas', 'latitude' => 16.2800, 'longitude' => 120.3800],
            ['name' => 'Bangar', 'latitude' => 16.9000, 'longitude' => 120.4200],
            ['name' => 'Burgos', 'latitude' => 16.7500, 'longitude' => 120.4500],
            ['name' => 'Bagulin', 'latitude' => 16.6000, 'longitude' => 120.5000],
            ['name' => 'Santol', 'latitude' => 16.4800, 'longitude' => 120.5200],
            ['name' => 'Sudipen', 'latitude' => 16.5800, 'longitude' => 120.4800],
        ];

        foreach ($municipalities as $m) {
            Municipality::updateOrCreate(['name' => $m['name']], $m);
        }
    }
}
