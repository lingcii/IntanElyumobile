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
        Schema::table('itinerary_items', function (Blueprint $table) {
            if (!Schema::hasColumn('itinerary_items', 'transport_mode')) {
                $table->string('transport_mode')->nullable()->after('tourist_spot_id');
            }
            if (!Schema::hasColumn('itinerary_items', 'leg_cost')) {
                $table->decimal('leg_cost', 10, 2)->default(0.00)->after('transport_mode');
            }
            if (!Schema::hasColumn('itinerary_items', 'leg_distance_km')) {
                $table->decimal('leg_distance_km', 8, 2)->nullable()->after('leg_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itinerary_items', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('itinerary_items', 'transport_mode')) {
                $cols[] = 'transport_mode';
            }
            if (Schema::hasColumn('itinerary_items', 'leg_cost')) {
                $cols[] = 'leg_cost';
            }
            if (Schema::hasColumn('itinerary_items', 'leg_distance_km')) {
                $cols[] = 'leg_distance_km';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
