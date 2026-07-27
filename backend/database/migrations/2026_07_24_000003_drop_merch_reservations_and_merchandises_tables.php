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
        Schema::dropIfExists('merch_reservations');
        Schema::dropIfExists('merchandises');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('merchandises', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('Apparel');
            $table->integer('price_xp')->default(0);
            $table->string('image')->nullable();
            $table->string('badge')->nullable();
            $table->integer('stock')->default(0);
            $table->timestamps();
        });

        Schema::create('merch_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('merchandise_id')->constrained('merchandises')->onDelete('cascade');
            $table->enum('status', ['pending', 'claimed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }
};
