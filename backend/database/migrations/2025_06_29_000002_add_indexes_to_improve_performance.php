<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = [
            'tourist_spots' => [
                ['status', 'municipality_id'],
                ['category'],
                ['classification_status'],
                ['visits'],
                ['rating'],
                ['created_at'],
            ],
            'analytics' => [
                ['date'],
                ['municipality_id', 'date'],
            ],
            'users' => [
                ['role', 'status'],
                ['status'],
            ],
            'alerts' => [
                ['is_read', 'created_at'],
            ],
            'tourist_spot_images' => [
                ['spot_id', 'is_primary'],
            ],
        ];

        foreach ($indexes as $table => $cols) {
            foreach ($cols as $col) {
                try {
                    Schema::table($table, function (Blueprint $t) use ($col) {
                        $t->index($col);
                    });
                } catch (\Exception $e) {
                    // Index may already exist
                }
            }
        }
    }

    public function down(): void
    {
        $indexes = [
            'tourist_spots' => [
                ['status', 'municipality_id'],
                ['category'],
                ['classification_status'],
                ['visits'],
                ['rating'],
                ['created_at'],
            ],
            'analytics' => [
                ['date'],
                ['municipality_id', 'date'],
            ],
            'users' => [
                ['role', 'status'],
                ['status'],
            ],
            'alerts' => [
                ['is_read', 'created_at'],
            ],
            'tourist_spot_images' => [
                ['spot_id', 'is_primary'],
            ],
        ];

        foreach ($indexes as $table => $cols) {
            foreach ($cols as $col) {
                try {
                    Schema::table($table, function (Blueprint $t) use ($col) {
                        $t->dropIndex($col);
                    });
                } catch (\Exception $e) {
                    // Index may not exist
                }
            }
        }
    }
};
