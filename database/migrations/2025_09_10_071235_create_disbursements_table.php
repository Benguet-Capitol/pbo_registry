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
        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obligation_id')->constrained('obligations')->onDelete('cascade');
            $table->foreignId('obligation_amounts_id')->constrained('obligation_amounts')->onDelete('cascade');
            $table->string('dv_no')->unique();
            $table->string('remarks')->nullable();
            $table->date('disbursement_date');
            $table->decimal('disbursement_amount', 15, 2);
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
