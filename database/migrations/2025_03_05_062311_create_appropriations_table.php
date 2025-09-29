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
        Schema::create('appropriations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('office_allotment_class_id');
            $table->integer('program_no');
            $table->string('programs');
            $table->string('project_no');
            $table->string('account_code');
            $table->string('id2');
            $table->string('description');
            $table->string('fpp_code');
            $table->string('project_location');
            $table->string('cco_year');
            $table->double('appropriation', 15, 2);
            $table->double('quarter1', 15, 2);
            $table->double('quarter2', 15, 2);
            $table->double('quarter3', 15, 2);
            $table->double('quarter4', 15, 2);
            $table->double('buffer_fund', 15, 2);
            $table->string('remarks');
            $table->integer('col');
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('office_allotment_class_id')->references('id')->on('office_allotment_classes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appropriations');
    }
};