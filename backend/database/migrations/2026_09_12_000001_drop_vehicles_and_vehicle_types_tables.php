<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('tourist_spot_vehicle_type');
        Schema::dropIfExists('vehicle_types');
        Schema::dropIfExists('vehicles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->decimal('fuel_efficiency_kml', 8, 2)->default(12.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('vehicle_types')) {
            Schema::create('vehicle_types', function (Blueprint $table) {
                $table->id();
                $table->string('category');
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tourist_spot_vehicle_type')) {
            Schema::create('tourist_spot_vehicle_type', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tourist_spot_id');
                $table->unsignedBigInteger('vehicle_type_id');
                $table->foreign('tourist_spot_id')->references('id')->on('tourist_spots')->onDelete('cascade');
                $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types')->onDelete('cascade');
                $table->unique(['tourist_spot_id', 'vehicle_type_id'], 'ts_vt_unique');
            });
        }
    }
};
