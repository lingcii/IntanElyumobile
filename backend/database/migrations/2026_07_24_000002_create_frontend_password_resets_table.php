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
        if (!Schema::hasTable('frontend_password_resets')) {
            Schema::create('frontend_password_resets', function (Blueprint $table) {
                $table->id();
                $table->string('email')->index();
                $table->string('token_hash');
                $table->timestamp('expires_at');
                $table->boolean('used')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frontend_password_resets');
    }
};
