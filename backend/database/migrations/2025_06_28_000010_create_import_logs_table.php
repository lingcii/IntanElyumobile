<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fare_upload_id');
            $table->string('action');
            $table->string('message');
            $table->text('details')->nullable();
            $table->timestamps();
            
            $table->foreign('fare_upload_id')->references('id')->on('fare_uploads')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
