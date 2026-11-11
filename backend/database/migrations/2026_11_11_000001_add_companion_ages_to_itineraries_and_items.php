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
        Schema::table('itineraries', function (Blueprint $table) {
            if (!Schema::hasColumn('itineraries', 'companion_ages')) {
                $table->json('companion_ages')->nullable()->after('companion_pwds');
            }
        });

        Schema::table('itinerary_items', function (Blueprint $table) {
            if (!Schema::hasColumn('itinerary_items', 'companion_ages')) {
                $table->json('companion_ages')->nullable()->after('companion_pwds');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            if (Schema::hasColumn('itineraries', 'companion_ages')) {
                $table->dropColumn('companion_ages');
            }
        });

        Schema::table('itinerary_items', function (Blueprint $table) {
            if (Schema::hasColumn('itinerary_items', 'companion_ages')) {
                $table->dropColumn('companion_ages');
            }
        });
    }
};
