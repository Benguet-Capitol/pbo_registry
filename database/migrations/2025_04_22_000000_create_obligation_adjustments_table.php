<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObligationAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('obligation_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obligation_id')->constrained('obligations')->onDelete('cascade');
            $table->foreignId('obligation_amounts_id')->constrained('obligation_amounts')->onDelete('cascade');
            $table->date('adjustment_date');
            $table->string('adjustment_remarks')->nullable();
            $table->decimal('adjustment_amount', 15, 2);
            $table->string('adjustment_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('obligation_adjustments');
    }
}