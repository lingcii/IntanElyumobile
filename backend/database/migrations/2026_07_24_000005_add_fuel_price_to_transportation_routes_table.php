<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transportation_routes', function (Blueprint $table) {
            if (!Schema::hasColumn('transportation_routes', 'fuel_price')) {
                $table->decimal('fuel_price', 8, 2)->nullable()->default(65.00)->after('vehicle_type');
            }
        });

        // Set default fuel price for any existing route records
        try {
            DB::table('transportation_routes')->whereNull('fuel_price')->update(['fuel_price' => 65.00]);
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transportation_routes', function (Blueprint $table) {
            if (Schema::hasColumn('transportation_routes', 'fuel_price')) {
                $table->dropColumn('fuel_price');
            }
        });
    }
};
