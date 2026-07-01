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
        Schema::table('tourist_spots', function (Blueprint $table) {
            $table->string('barangay', 255)->nullable()->after('municipality_id');
            $table->timestamp('updated_at')->nullable()->after('created_at');
            $table->string('category', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tourist_spots', function (Blueprint $table) {
            $table->dropColumn(['barangay', 'updated_at']);
        });
    }
};
