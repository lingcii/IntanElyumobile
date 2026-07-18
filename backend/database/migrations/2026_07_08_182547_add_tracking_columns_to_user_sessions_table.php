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
        if (!Schema::hasTable('user_sessions')) {
            Schema::create('user_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('user_agent')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('platform')->nullable();
                $table->timestamp('last_activity')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('user_sessions', function (Blueprint $table) {
                $table->string('platform')->nullable()->after('user_agent');
                $table->timestamp('last_activity')->nullable()->after('created_at');
                $table->boolean('is_active')->default(true)->after('expires_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_sessions')) {
            Schema::table('user_sessions', function (Blueprint $table) {
                $table->dropColumn(['platform', 'last_activity', 'is_active']);
            });
        }
    }
};
