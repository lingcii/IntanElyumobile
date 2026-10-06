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
        if (!Schema::hasColumn('itinerary_items', 'order_index')) {
            Schema::table('itinerary_items', function (Blueprint $table) {
                $table->unsignedInteger('order_index')->default(0)->after('tourist_spot_id');
            });
        }

        // Backfill existing itinerary items so their order matches their current sequential position
        try {
            $itineraryIds = DB::table('itinerary_items')->distinct()->pluck('itinerary_id');
            foreach ($itineraryIds as $itineraryId) {
                $items = DB::table('itinerary_items')
                    ->where('itinerary_id', $itineraryId)
                    ->orderBy('id', 'asc')
                    ->get();
                $seq = 1;
                foreach ($items as $item) {
                    DB::table('itinerary_items')
                        ->where('id', $item->id)
                        ->update(['order_index' => $seq++]);
                }
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('itinerary_items', 'order_index')) {
            Schema::table('itinerary_items', function (Blueprint $table) {
                $table->dropColumn('order_index');
            });
        }
    }
};
