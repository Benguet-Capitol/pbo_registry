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
        Schema::create('supplemental_files', function (Blueprint $table) {
            $table->id();
            $table->string('supplemental_no'); // Shared across supplementals with same number
            $table->string('file_name');
            $table->string('original_file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->bigInteger('file_size');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('supplemental_no');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplemental_files');
    }
};
