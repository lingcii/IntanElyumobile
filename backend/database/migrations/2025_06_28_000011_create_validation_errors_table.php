<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_errors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fare_upload_id');
            $table->integer('row_number');
            $table->string('field')->nullable();
            $table->string('error');
            $table->timestamps();
            
            $table->foreign('fare_upload_id')->references('id')->on('fare_uploads')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_errors');
    }
};
