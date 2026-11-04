<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['voucher_redemptions', 'point_redemptions'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)
                ->whereIn('status', ['claimed', 'active'])
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        $claimedAt = Carbon::parse($row->claimed_at ?? $row->created_at);
                        $claimDeadline = $claimedAt->copy()->addDays(3);
                        $currentDeadline = !empty($row->voucher_expires_at)
                            ? Carbon::parse($row->voucher_expires_at)
                            : null;

                        $updates = [
                            'voucher_expires_at' => $currentDeadline && $currentDeadline->lt($claimDeadline)
                                ? $currentDeadline
                                : $claimDeadline,
                        ];

                        if (strtolower($row->status) === 'claimed') {
                            $updates['qr_token'] = null;
                            $updates['qr_expires_at'] = null;
                        }

                        DB::table($table)->where('id', $row->id)->update($updates);
                    }
                });
        }
    }

    public function down(): void
    {
        // Previous expiry timestamps cannot be reconstructed safely.
    }
};
