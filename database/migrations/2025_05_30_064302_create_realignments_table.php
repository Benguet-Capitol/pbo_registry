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
        Schema::create('realignments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('office_allotment_classes_id');
            $table->unsignedBigInteger('appropriations_id');
            $table->string('realignment_no');
            $table->date('realignment_date');
            $table->decimal('amount', 10, 2);
            $table->string('type');
            $table->string('basis');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('office_allotment_classes_id')->references('id')->on('office_allotment_classes')->cascadeOnDelete();
            $table->foreign('appropriations_id')->references('id')->on('appropriations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realignments');
    }
};
