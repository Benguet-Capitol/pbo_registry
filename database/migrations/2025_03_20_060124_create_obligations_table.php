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
    Schema::create('obligations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('office_allotment_class_id');
        $table->unsignedBigInteger('appropriation_id');
        $table->string('obr_no');
        $table->string('obr_type');
        $table->date('obr_date');
        $table->text('particulars');
        $table->decimal('obr_amount', 10, 2);
        $table->text('remarks')->nullable();
        $table->timestamps();

        $table->foreign('office_allotment_class_id')->references('id')->on('office_allotment_classes')->cascadeOnDelete();
        $table->foreign('appropriation_id')->references('id')->on('appropriations')->cascadeOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obligations');
    }
};
