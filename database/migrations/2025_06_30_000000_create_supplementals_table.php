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
        Schema::create('supplementals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('office_allotment_classes_id');
            $table->unsignedBigInteger('appropriations_id');
            $table->string('basis_no');
            $table->string('supplemental_no');
            $table->date('supplemental_date');
            $table->text('basis');
            $table->string('type');
            $table->decimal('amount', 20, 2);
            $table->decimal('quarter1', 20, 2)->default(0);
            $table->decimal('quarter2', 20, 2)->default(0);
            $table->decimal('quarter3', 20, 2)->default(0);
            $table->decimal('quarter4', 20, 2)->default(0);
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
        Schema::dropIfExists('supplementals');
    }
};
