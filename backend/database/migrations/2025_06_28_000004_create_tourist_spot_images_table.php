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
        Schema::create('tourist_spot_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spot_id')->constrained('tourist_spots')->onDelete('cascade');
            $table->string('photo_url');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tourist_spot_images');
    }
};
