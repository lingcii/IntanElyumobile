<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('itinerary_items', 'proof_status')) {
            Schema::table('itinerary_items', function (Blueprint $table) {
                $table->string('proof_status', 20)->nullable()->after('proof_image');
            });
        }

        if (!Schema::hasColumn('itinerary_items', 'reviewed_by')) {
            Schema::table('itinerary_items', function (Blueprint $table) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('proof_status');
            });
        }

        if (!Schema::hasColumn('itinerary_items', 'reviewed_at')) {
            Schema::table('itinerary_items', function (Blueprint $table) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            });
        }

        if (!Schema::hasColumn('itinerary_items', 'rejection_reason')) {
            Schema::table('itinerary_items', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('reviewed_at');
            });
        }
    }

    public function down(): void
    {
        // Intentionally preserve proof-review history on rollback.
    }
};
