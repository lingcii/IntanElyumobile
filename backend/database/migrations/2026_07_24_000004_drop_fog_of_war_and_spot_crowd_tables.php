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
        Schema::dropIfExists('fog_of_war_tiles');
        Schema::dropIfExists('spot_crowd_stats');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('fog_of_war_tiles')) {
            Schema::create('fog_of_war_tiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('tile_key', 32);
                $table->timestamp('revealed_at')->useCurrent();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['user_id', 'tile_key']);
                $table->index('user_id');
            });
        }

        if (!Schema::hasTable('spot_crowd_stats')) {
            Schema::create('spot_crowd_stats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('spot_id');
                $table->integer('checkin_count')->default(0);
                $table->dateTime('window_start');
                $table->dateTime('window_end');
                $table->string('crowd_level', 10)->default('low');
                $table->decimal('xp_multiplier', 3, 2)->default(1.00);

                $table->foreign('spot_id')->references('id')->on('tourist_spots')->onDelete('cascade');
                $table->index(['spot_id', 'window_start']);
            });
        }
    }
};
