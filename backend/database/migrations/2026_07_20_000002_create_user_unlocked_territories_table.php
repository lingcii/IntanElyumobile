<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_unlocked_territories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('municipality_id');
            $table->timestamp('unlocked_at')->useCurrent();
            $table->unique(['user_id', 'municipality_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_unlocked_territories');
    }
};
