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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obligation_id')->constrained('obligations')->onDelete('cascade');
            $table->foreignId('obligation_amounts_id')->constrained('obligation_amounts')->onDelete('cascade');
            $table->string('po_number')->unique();
            $table->string('pr_no');
            $table->date('po_date');
            $table->string('po_type');
            $table->string('status');
            $table->string('po_remarks');
            $table->string('supplier');
            $table->string('delivery_period');
            $table->date('delivery_date');
            $table->decimal('po_amount', 15, 2);
            $table->decimal('delivery_amount', 15, 2);
            $table->string('delivery_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
