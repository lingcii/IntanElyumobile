<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            ['name' => 'Tricycle', 'description' => 'Three-wheeled vehicle for short trips', 'icon' => 'fa-motorcycle', 'is_active' => true],
            ['name' => 'Jeepney', 'description' => 'Iconic Philippine public transport', 'icon' => 'fa-bus', 'is_active' => true],
            ['name' => 'Bus', 'description' => 'Large public transport for longer routes', 'icon' => 'fa-bus-alt', 'is_active' => true],
            ['name' => 'Van', 'description' => 'Private or shared van transport', 'icon' => 'fa-shuttle-van', 'is_active' => true],
            ['name' => 'Taxi', 'description' => 'Private taxi service', 'icon' => 'fa-taxi', 'is_active' => true],
            ['name' => 'Motorcycle', 'description' => 'Two-wheeled vehicle', 'icon' => 'fa-motorcycle', 'is_active' => true],
            ['name' => 'Private Car', 'description' => 'Personal vehicle', 'icon' => 'fa-car', 'is_active' => true],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(['name' => $vehicle['name']], $vehicle);
        }
    }
}
