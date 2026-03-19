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
        Schema::create('obligation_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obligation_id')->constrained('obligations')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('original_file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->bigInteger('file_size');
            $table->string('uploaded_by')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('obligation_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obligation_files');
    }
};
