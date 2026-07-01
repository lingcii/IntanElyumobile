<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fare_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->string('status')->default('draft');
            $table->integer('total_records')->default(0);
            $table->integer('valid_records')->default(0);
            $table->integer('invalid_records')->default(0);
            $table->unsignedBigInteger('fare_guide_id')->nullable();
            $table->timestamps();
            
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('fare_guide_id')->references('id')->on('fare_guides')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fare_uploads');
    }
};
