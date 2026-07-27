<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Change the `category` column on tourist_spots from ENUM to VARCHAR(255).
 * This allows storing comma-separated multi-category values like "Beach,Mountain".
 * Existing single-value rows are preserved unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        // MySQL won't let you ALTER an ENUM to VARCHAR directly with Blueprint
        // when there are existing rows — use a raw statement instead.
        DB::statement("ALTER TABLE tourist_spots MODIFY COLUMN category VARCHAR(255) NOT NULL DEFAULT 'Other'");
    }

    public function down(): void
    {
        // Revert to the original ENUM (single-value only)
        DB::statement("ALTER TABLE tourist_spots MODIFY COLUMN category ENUM('Beach','Mountain','Historical','Waterfalls','Adventure','Farm','Religious','Other') NOT NULL DEFAULT 'Other'");
    }
};
