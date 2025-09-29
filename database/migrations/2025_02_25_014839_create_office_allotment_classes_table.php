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
        Schema::create('office_allotment_classes', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->string('office');
            $table->string('sub_office');
            $table->string('office_abbreviation');
            $table->string('fund');
            $table->string('fund_source');
            $table->string('class');
            $table->string('fpp');
            $table->string('resp_code');
            $table->date('date_approved');
            $table->string('mfo_services');
            $table->integer('no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_allotment_classes');
    }
};
