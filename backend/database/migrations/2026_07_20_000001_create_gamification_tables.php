<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Quests (themed multi-stop itineraries) ──────────────────────
        if (!Schema::hasTable('quests')) {
            Schema::create('quests', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('theme_icon')->default('🗺️');       // emoji icon
                $table->string('theme_color')->default('#38bdf8');   // hex color
                $table->decimal('required_hours', 4, 1);             // e.g. 3.0
                $table->json('spot_ids');                            // array of tourist_spot IDs
                $table->integer('xp_reward')->default(300);
                $table->string('badge_name')->nullable();
                $table->string('badge_icon')->default('🏅');
                $table->string('category')->nullable();              // Beach, Heritage, Food, etc.
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // ── 2. Quest completions ────────────────────────────────────────────
        if (!Schema::hasTable('quest_completions')) {
            Schema::create('quest_completions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('quest_id');
                $table->integer('xp_earned')->default(0);
                $table->timestamp('completed_at')->useCurrent();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('quest_id')->references('id')->on('quests')->onDelete('cascade');
                $table->unique(['user_id', 'quest_id']); // one completion per quest per user
            });
        }

        // ── 3. Fog of War tiles (Leaflet zoom-15 tile key) ─────────────────
        // tile_key format: "zoom_x_y" (e.g. "15_26432_15288")
        if (!Schema::hasTable('fog_of_war_tiles')) {
            Schema::create('fog_of_war_tiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('tile_key', 32);   // "15_x_y"
                $table->timestamp('revealed_at')->useCurrent();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['user_id', 'tile_key']);
                $table->index('user_id');
            });
        }

        // ── 4. Spot crowd stats (hourly rolling windows) ────────────────────
        if (!Schema::hasTable('spot_crowd_stats')) {
            Schema::create('spot_crowd_stats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('spot_id');
                $table->integer('checkin_count')->default(0);
                $table->dateTime('window_start');
                $table->dateTime('window_end');
                $table->string('crowd_level', 10)->default('low'); // low|medium|high
                $table->decimal('xp_multiplier', 3, 2)->default(1.00);

                $table->foreign('spot_id')->references('id')->on('tourist_spots')->onDelete('cascade');
                $table->index(['spot_id', 'window_start']);
            });
        }

        // ── 5. AR Check-ins ─────────────────────────────────────────────────
        if (!Schema::hasTable('ar_checkins')) {
            Schema::create('ar_checkins', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('spot_id');
                $table->unsignedBigInteger('trivia_question_id')->nullable();
                $table->boolean('trivia_correct')->default(false);
                $table->decimal('user_lat', 10, 7)->nullable();
                $table->decimal('user_lng', 10, 7)->nullable();
                $table->integer('xp_earned')->default(0);
                $table->timestamp('checked_in_at')->useCurrent();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('spot_id')->references('id')->on('tourist_spots')->onDelete('cascade');
                $table->index(['user_id', 'spot_id']);
            });
        }

        // ── 6. Trivia questions ─────────────────────────────────────────────
        if (!Schema::hasTable('trivia_questions')) {
            Schema::create('trivia_questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('spot_id')->nullable(); // null = general La Union trivia
                $table->text('question');
                $table->json('options');                   // ["A","B","C","D"]
                $table->unsignedTinyInteger('correct_index'); // 0-based
                $table->string('difficulty', 10)->default('medium'); // easy|medium|hard
                $table->text('fun_fact')->nullable();      // shown after answer
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('spot_id')->references('id')->on('tourist_spots')->onDelete('set null');
                $table->index('spot_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trivia_questions');
        Schema::dropIfExists('ar_checkins');
        Schema::dropIfExists('spot_crowd_stats');
        Schema::dropIfExists('fog_of_war_tiles');
        Schema::dropIfExists('quest_completions');
        Schema::dropIfExists('quests');
    }
};
