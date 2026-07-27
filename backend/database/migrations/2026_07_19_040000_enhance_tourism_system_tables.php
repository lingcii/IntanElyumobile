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
        // 1. Add fuel_efficiency_kml to vehicles table
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'fuel_efficiency_kml')) {
                $table->decimal('fuel_efficiency_kml', 5, 2)->default(12.00)->after('icon');
            }
        });

        // 2. Add photo_url to site_feedbacks table
        Schema::table('site_feedbacks', function (Blueprint $table) {
            if (!Schema::hasColumn('site_feedbacks', 'photo_url')) {
                $table->string('photo_url')->nullable()->after('safety_level');
            }
        });

        // 3. Create fare_confirmations table with matching unsignedBigInteger foreign keys
        if (!Schema::hasTable('fare_confirmations')) {
            Schema::create('fare_confirmations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('fare_matrix_id')->nullable();
                $table->boolean('is_correct');
                $table->decimal('reported_fare', 10, 2)->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('fare_matrix_id')->references('id')->on('fare_matrices')->onDelete('cascade');
            });
        }

        // 4. Create system_settings table for dynamic configs (like fuel prices)
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // 5. Seed/Update vehicle efficiencies
        try {
            DB::table('vehicles')->where('name', 'Tricycle')->update(['fuel_efficiency_kml' => 25.00]);
            DB::table('vehicles')->where('name', 'Jeepney')->update(['fuel_efficiency_kml' => 8.00]);
            DB::table('vehicles')->where('name', 'Bus')->update(['fuel_efficiency_kml' => 4.00]);
            DB::table('vehicles')->where('name', 'Van')->update(['fuel_efficiency_kml' => 10.00]);
            DB::table('vehicles')->where('name', 'Taxi')->update(['fuel_efficiency_kml' => 12.00]);
            DB::table('vehicles')->where('name', 'Motorcycle')->update(['fuel_efficiency_kml' => 35.00]);
            DB::table('vehicles')->where('name', 'Private Car')->update(['fuel_efficiency_kml' => 12.00]);
        } catch (\Exception $e) {
            // Ignore if vehicles table is not seeded yet
        }

        // 6. Seed system setting for fuel price
        try {
            DB::table('system_settings')->updateOrInsert(
                ['key' => 'fuel_price'],
                [
                    'value' => '65.00',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        } catch (\Exception $e) {
            // Ignore if system_settings table does not exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('fare_confirmations');

        Schema::table('site_feedbacks', function (Blueprint $table) {
            if (Schema::hasColumn('site_feedbacks', 'photo_url')) {
                $table->dropColumn('photo_url');
            }
        });

        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'fuel_efficiency_kml')) {
                $table->dropColumn('fuel_efficiency_kml');
            }
        });
    }
};
