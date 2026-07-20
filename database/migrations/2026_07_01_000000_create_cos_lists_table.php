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
        Schema::create('cos_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_allotment_class_id')
                ->constrained('office_allotment_classes')
                ->onDelete('cascade');
            $table->foreignId('appropriation_id')
                ->constrained('appropriations')
                ->onDelete('cascade');
            $table->string('employee_id')->nullable();
            $table->string('employee_name');
            $table->string('position_title');
            $table->string('salary_grade')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->decimal('monthly_rate', 15, 2)->default(0);
            $table->decimal('annual_rate', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->text('basis')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index('office_allotment_class_id');
            $table->index('appropriation_id');
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cos_lists');
    }
};