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
            if (!Schema::hasColumn('itineraries', 'companion_adults')) {
                $table->unsignedInteger('companion_adults')->default(0)->after('companion_females');
            }
            if (!Schema::hasColumn('itineraries', 'companion_kids')) {
                $table->unsignedInteger('companion_kids')->default(0)->after('companion_adults');
            }
            if (!Schema::hasColumn('itineraries', 'companion_seniors')) {
                $table->unsignedInteger('companion_seniors')->default(0)->after('companion_kids');
            }
            if (!Schema::hasColumn('itineraries', 'companion_pwds')) {
                $table->unsignedInteger('companion_pwds')->default(0)->after('companion_seniors');
            }
        });

        Schema::table('itinerary_items', function (Blueprint $table) {
            if (!Schema::hasColumn('itinerary_items', 'companion_adults')) {
                $table->unsignedInteger('companion_adults')->default(0)->after('companion_females');
            }
            if (!Schema::hasColumn('itinerary_items', 'companion_kids')) {
                $table->unsignedInteger('companion_kids')->default(0)->after('companion_adults');
            }
            if (!Schema::hasColumn('itinerary_items', 'companion_seniors')) {
                $table->unsignedInteger('companion_seniors')->default(0)->after('companion_kids');
            }
            if (!Schema::hasColumn('itinerary_items', 'companion_pwds')) {
                $table->unsignedInteger('companion_pwds')->default(0)->after('companion_seniors');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            $cols = ['companion_adults', 'companion_kids', 'companion_seniors', 'companion_pwds'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('itineraries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('itinerary_items', function (Blueprint $table) {
            $cols = ['companion_adults', 'companion_kids', 'companion_seniors', 'companion_pwds'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('itinerary_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

