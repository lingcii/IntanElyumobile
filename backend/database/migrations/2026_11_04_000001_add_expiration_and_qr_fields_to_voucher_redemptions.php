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
        // 1. Ensure voucher_redemptions table exists with full schema
        if (!Schema::hasTable('voucher_redemptions')) {
            Schema::create('voucher_redemptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('voucher_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('redemption_code')->unique();
                $table->string('qr_token')->nullable()->unique();
                $table->integer('points_used')->default(0);
                $table->string('reward_name_snapshot')->nullable();
                $table->string('status')->default('claimed'); // available, claimed, used, cancelled, expired
                $table->timestamp('voucher_expires_at')->nullable()->index();
                $table->timestamp('qr_expires_at')->nullable()->index();
                $table->timestamp('claimed_at')->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->unsignedBigInteger('redeemed_by_partner_id')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('voucher_redemptions', function (Blueprint $table) {
                if (!Schema::hasColumn('voucher_redemptions', 'qr_token')) {
                    $table->string('qr_token')->nullable()->unique()->after('redemption_code');
                }
                if (!Schema::hasColumn('voucher_redemptions', 'voucher_expires_at')) {
                    $table->timestamp('voucher_expires_at')->nullable()->index()->after('status');
                }
                if (!Schema::hasColumn('voucher_redemptions', 'qr_expires_at')) {
                    $table->timestamp('qr_expires_at')->nullable()->index()->after('voucher_expires_at');
                }
                if (!Schema::hasColumn('voucher_redemptions', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('redeemed_at');
                }
            });
        }

        // 2. Also enhance point_redemptions for dual-table compatibility
        if (Schema::hasTable('point_redemptions')) {
            Schema::table('point_redemptions', function (Blueprint $table) {
                if (!Schema::hasColumn('point_redemptions', 'voucher_id')) {
                    $table->unsignedBigInteger('voucher_id')->nullable()->index()->after('id');
                }
                if (!Schema::hasColumn('point_redemptions', 'qr_token')) {
                    $table->string('qr_token')->nullable()->unique()->after('voucher_code');
                }
                if (!Schema::hasColumn('point_redemptions', 'voucher_expires_at')) {
                    $table->timestamp('voucher_expires_at')->nullable()->index()->after('status');
                }
                if (!Schema::hasColumn('point_redemptions', 'qr_expires_at')) {
                    $table->timestamp('qr_expires_at')->nullable()->index()->after('voucher_expires_at');
                }
                if (!Schema::hasColumn('point_redemptions', 'claimed_at')) {
                    $table->timestamp('claimed_at')->nullable()->after('qr_expires_at');
                }
                if (!Schema::hasColumn('point_redemptions', 'redeemed_at')) {
                    $table->timestamp('redeemed_at')->nullable()->after('claimed_at');
                }
                if (!Schema::hasColumn('point_redemptions', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('redeemed_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('voucher_redemptions')) {
            Schema::table('voucher_redemptions', function (Blueprint $table) {
                $columns = ['qr_token', 'voucher_expires_at', 'qr_expires_at', 'cancelled_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('voucher_redemptions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('point_redemptions')) {
            Schema::table('point_redemptions', function (Blueprint $table) {
                $columns = ['voucher_id', 'qr_token', 'voucher_expires_at', 'qr_expires_at', 'claimed_at', 'redeemed_at', 'cancelled_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('point_redemptions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};

