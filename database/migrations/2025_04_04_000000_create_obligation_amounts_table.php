<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObligationAmountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('obligation_amounts', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('appropriation_id'); // Foreign key to appropriations table
            $table->unsignedBigInteger('obligation_id'); // Foreign key to obligations table
            $table->string('account_code'); // Account code
            $table->decimal('obr_amount', 15, 2); // Obligation amount with precision

            $table->timestamps(); // Created at and updated at timestamps

            // Foreign key constraints
            $table->foreign('appropriation_id')->references('id')->on('appropriations')->onDelete('cascade');
            $table->foreign('obligation_id')->references('id')->on('obligations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('obligation_amounts');
    }
}