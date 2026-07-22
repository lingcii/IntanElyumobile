<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tourist_spot_id')->nullable()->constrained('tourist_spots')->onDelete('cascade'); // null = general policy recommendation
            $table->integer('rating')->nullable();
            $table->text('testimony')->nullable();
            $table->text('policy_recommendation')->nullable();
            $table->string('crowd_level')->nullable(); // low, medium, high
            $table->string('cleanliness_level')->nullable(); // clean, moderate, dirty
            $table->string('safety_level')->nullable(); // safe, moderate, unsafe
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_feedbacks');
    }
};
