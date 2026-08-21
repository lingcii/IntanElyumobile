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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('points')->default(0)->after('xp');
            });

            // Backfill points from user_points and point_redemptions
            try {
                DB::statement("
                    UPDATE users u
                    SET u.points = GREATEST(
                        0,
                        COALESCE((SELECT SUM(up.points) FROM user_points up WHERE up.user_id = u.id), 0) -
                        COALESCE((SELECT SUM(pr.points_cost) FROM point_redemptions pr WHERE pr.user_id = u.id), 0)
                    )
                    WHERE u.role = 'tourist'
                ");
            } catch (\Throwable $e) {
                // Ignore backfill errors
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('points');
            });
        }
    }
};
