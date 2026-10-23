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
        if (!Schema::hasTable('tourism_seasons')) {
            Schema::create('tourism_seasons', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->string('slug', 100)->unique();
                $table->enum('type', ['annual_season', 'event_festival', 'weekend_surge', 'custom'])->default('annual_season');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->unsignedTinyInteger('start_month')->nullable(); // 1-12
                $table->unsignedTinyInteger('end_month')->nullable();   // 1-12
                $table->json('applicable_days')->nullable();
                $table->string('municipality', 100)->nullable();
                $table->decimal('fare_surcharge_percent', 5, 2)->default(20.00);
                $table->decimal('cost_multiplier', 3, 2)->default(1.20);
                $table->decimal('traffic_delay_multiplier', 3, 2)->default(1.25);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tourism_seasons');
    }
};
