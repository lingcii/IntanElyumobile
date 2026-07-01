<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Indexes for tourist_spots table
        Schema::table('tourist_spots', function (Blueprint $table) {
            $table->index(['status', 'municipality_id']);
            $table->index(['category']);
            $table->index(['classification_status']);
            $table->index(['visits']);
            $table->index(['rating']);
            $table->index(['created_at']);
        });

        // Indexes for analytics table
        Schema::table('analytics', function (Blueprint $table) {
            $table->index(['year', 'month']);
            $table->index(['municipality_id', 'year', 'month']);
        });

        // Indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'status']);
            $table->index(['status']);
        });

        // Indexes for alerts table
        Schema::table('alerts', function (Blueprint $table) {
            $table->index(['is_read', 'created_at']);
        });

        // Indexes for tourist_spot_images
        Schema::table('tourist_spot_images', function (Blueprint $table) {
            $table->index(['spot_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::table('tourist_spots', function (Blueprint $table) {
            $table->dropIndex(['status', 'municipality_id']);
            $table->dropIndex(['category']);
            $table->dropIndex(['classification_status']);
            $table->dropIndex(['visits']);
            $table->dropIndex(['rating']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('analytics', function (Blueprint $table) {
            $table->dropIndex(['year', 'month']);
            $table->dropIndex(['municipality_id', 'year', 'month']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'status']);
            $table->dropIndex(['status']);
        });

        Schema::table('alerts', function (Blueprint $table) {
            $table->dropIndex(['is_read', 'created_at']);
        });

        Schema::table('tourist_spot_images', function (Blueprint $table) {
            $table->dropIndex(['spot_id', 'is_primary']);
        });
    }
};
