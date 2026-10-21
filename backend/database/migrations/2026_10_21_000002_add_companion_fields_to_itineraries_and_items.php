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
            if (!Schema::hasColumn('itineraries', 'trip_type')) {
                $table->string('trip_type', 30)->default('solo')->after('status');
            }
            if (!Schema::hasColumn('itineraries', 'companion_males')) {
                $table->unsignedInteger('companion_males')->default(0)->after('trip_type');
            }
            if (!Schema::hasColumn('itineraries', 'companion_females')) {
                $table->unsignedInteger('companion_females')->default(0)->after('companion_males');
            }
            if (!Schema::hasColumn('itineraries', 'total_pax')) {
                $table->unsignedInteger('total_pax')->default(1)->after('companion_females');
            }
        });

        Schema::table('itinerary_items', function (Blueprint $table) {
            if (!Schema::hasColumn('itinerary_items', 'companion_males')) {
                $table->unsignedInteger('companion_males')->default(0)->after('is_visited');
            }
            if (!Schema::hasColumn('itinerary_items', 'companion_females')) {
                $table->unsignedInteger('companion_females')->default(0)->after('companion_males');
            }
            if (!Schema::hasColumn('itinerary_items', 'total_pax')) {
                $table->unsignedInteger('total_pax')->default(1)->after('companion_females');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            $cols = ['trip_type', 'companion_males', 'companion_females', 'total_pax'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('itineraries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('itinerary_items', function (Blueprint $table) {
            $cols = ['companion_males', 'companion_females', 'total_pax'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('itinerary_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
