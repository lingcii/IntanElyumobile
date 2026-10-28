<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('site_feedbacks', 'itinerary_item_id')) {
            return;
        }

        Schema::table('site_feedbacks', function (Blueprint $table) {
            $table->foreignId('itinerary_item_id')
                ->nullable()
                ->after('tourist_spot_id')
                ->constrained('itinerary_items')
                ->nullOnDelete();
            $table->index(['user_id', 'itinerary_item_id']);
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('site_feedbacks', 'itinerary_item_id')) {
            return;
        }

        Schema::table('site_feedbacks', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'itinerary_item_id']);
            $table->dropConstrainedForeignId('itinerary_item_id');
        });
    }
};
