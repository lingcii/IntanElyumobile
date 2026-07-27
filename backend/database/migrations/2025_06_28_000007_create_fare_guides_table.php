<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fare_guides', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('vehicle_type');
            $table->string('region');
            $table->date('effective_date')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_archived')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('archived_by')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->string('archived_reason')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fare_guides');
    }
};
