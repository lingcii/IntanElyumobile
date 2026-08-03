<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('icon')->nullable();
                $table->decimal('fuel_efficiency_kml', 5, 2)->default(12.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $vehicles = [
            ['name' => 'Tricycle', 'description' => 'Three-wheeled vehicle for short trips', 'icon' => 'fa-motorcycle', 'fuel_efficiency_kml' => 25.00, 'is_active' => true],
            ['name' => 'Jeepney', 'description' => 'Iconic Philippine public transport', 'icon' => 'fa-bus', 'fuel_efficiency_kml' => 8.00, 'is_active' => true],
            ['name' => 'Bus', 'description' => 'Large public transport for longer routes', 'icon' => 'fa-bus-alt', 'fuel_efficiency_kml' => 4.00, 'is_active' => true],
            ['name' => 'Van', 'description' => 'Private or shared van transport', 'icon' => 'fa-shuttle-van', 'fuel_efficiency_kml' => 10.00, 'is_active' => true],
            ['name' => 'Taxi', 'description' => 'Private taxi service', 'icon' => 'fa-taxi', 'fuel_efficiency_kml' => 12.00, 'is_active' => true],
            ['name' => 'Motorcycle', 'description' => 'Two-wheeled vehicle', 'icon' => 'fa-motorcycle', 'fuel_efficiency_kml' => 35.00, 'is_active' => true],
            ['name' => 'Private Car', 'description' => 'Personal vehicle', 'icon' => 'fa-car', 'fuel_efficiency_kml' => 12.00, 'is_active' => true],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(['name' => $vehicle['name']], $vehicle);
        }
    }
}

