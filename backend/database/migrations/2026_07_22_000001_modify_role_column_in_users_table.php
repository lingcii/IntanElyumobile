<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE users DROP CHECK users_role_check");
        } catch (\Exception $e) {}

        try {
            DB::statement("ALTER TABLE tourist_spots DROP CHECK tourist_spots_status_check");
        } catch (\Exception $e) {}

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 100)->default('tourist')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default('tourist')->change();
        });
    }
};
