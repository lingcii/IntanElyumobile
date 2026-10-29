<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('site_feedbacks', 'itinerary_item_id')) {
            return;
        }

        if (Schema::hasIndex('site_feedbacks', 'site_feedbacks_user_spot_unique')) {
            Schema::table('site_feedbacks', function (Blueprint $table) {
                $table->dropUnique('site_feedbacks_user_spot_unique');
            });
        }
        if (!Schema::hasIndex('site_feedbacks', 'site_feedbacks_user_item_unique')) {
            Schema::table('site_feedbacks', function (Blueprint $table) {
                $table->unique(['user_id', 'itinerary_item_id'], 'site_feedbacks_user_item_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('site_feedbacks', 'itinerary_item_id')) {
            return;
        }

        if (Schema::hasIndex('site_feedbacks', 'site_feedbacks_user_item_unique')) {
            Schema::table('site_feedbacks', function (Blueprint $table) {
                $table->dropUnique('site_feedbacks_user_item_unique');
            });
        }
        if (!Schema::hasIndex('site_feedbacks', 'site_feedbacks_user_spot_unique')) {
            Schema::table('site_feedbacks', function (Blueprint $table) {
                $table->unique(['user_id', 'tourist_spot_id'], 'site_feedbacks_user_spot_unique');
            });
        }
    }
};
