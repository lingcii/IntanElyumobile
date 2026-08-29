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
        if (Schema::hasTable('user_points') && !Schema::hasColumn('user_points', 'spot_id')) {
            Schema::table('user_points', function (Blueprint $table) {
                $table->foreignId('spot_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('tourist_spots')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_points') && Schema::hasColumn('user_points', 'spot_id')) {
            Schema::table('user_points', function (Blueprint $table) {
                $table->dropForeign(['spot_id']);
                $table->dropColumn('spot_id');
            });
        }
    }
};
