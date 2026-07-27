<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('municipality_id')->nullable();
            $table->unsignedBigInteger('tourist_spot_id')->nullable();
            $table->string('metric');
            $table->decimal('value', 12, 2);
            $table->date('date');
            $table->timestamps();
            
            $table->foreign('municipality_id')->references('id')->on('municipalities')->onDelete('cascade');
            $table->foreign('tourist_spot_id')->references('id')->on('tourist_spots')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics');
    }
};
