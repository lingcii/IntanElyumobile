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
        Schema::dropIfExists('fare_confirmations');
        Schema::dropIfExists('user_unlocked_territories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('fare_confirmations')) {
            Schema::create('fare_confirmations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('fare_matrix_id')->nullable();
                $table->boolean('is_correct');
                $table->decimal('reported_fare', 10, 2)->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('fare_matrix_id')->references('id')->on('fare_matrices')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('user_unlocked_territories')) {
            Schema::create('user_unlocked_territories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('municipality_id');
                $table->timestamp('unlocked_at')->useCurrent();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('municipality_id')->references('id')->on('municipalities')->onDelete('cascade');
                $table->unique(['user_id', 'municipality_id']);
            });
        }
    }
};
