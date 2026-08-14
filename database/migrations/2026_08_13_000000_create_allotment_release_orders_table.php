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
        Schema::create('allotment_release_orders', function (Blueprint $table) {
            $table->id();
            $table->string('aro_no')->unique();
            $table->date('date_of_issue');
            $table->year('year');
            $table->foreignId('office_allotment_classes_id')
                ->constrained('office_allotment_classes')
                ->onDelete('cascade');
            $table->enum('fund_source', ['Annual Budget', 'Supplemental Budget', 'Reenacted Budget']);
            $table->string('supplemental_no')->nullable();
            $table->string('ppa_code')->nullable();
            $table->foreignId('provincial_budget_officer_id')
                ->constrained('employees')
                ->onDelete('restrict');
            $table->string('provincial_budget_officer_title');
            $table->foreignId('provincial_governor_id')
                ->nullable()
                ->constrained('employees')
                ->onDelete('restrict');
            $table->string('provincial_governor_name')->nullable();
            $table->string('provincial_governor_title');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();

            $table->index(['office_allotment_classes_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allotment_release_orders');
    }
};
