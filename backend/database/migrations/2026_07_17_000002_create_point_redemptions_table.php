<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // pasalubong_discount, environmental_fee
            $table->integer('points_cost');
            $table->string('voucher_code')->unique();
            $table->string('status')->default('active'); // active, used, expired
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_redemptions');
    }
};
