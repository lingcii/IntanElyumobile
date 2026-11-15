<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tourist_spot_ordinance_images')) {
            Schema::create('tourist_spot_ordinance_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tourist_spot_id')->constrained('tourist_spots')->onDelete('cascade');
                $table->string('original_filename')->nullable();
                $table->string('stored_filename')->nullable();
                $table->string('cloudflare_path')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tourist_spot_ordinance_images');
    }
};
