<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fare_matrices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fare_guide_id');
            $table->decimal('distance_km', 8, 2);
            $table->decimal('regular_fare', 8, 2);
            $table->decimal('discounted_fare', 8, 2);
            $table->timestamps();
            
            $table->foreign('fare_guide_id')->references('id')->on('fare_guides')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fare_matrices');
    }
};
