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
        Schema::create('allotment_release_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allotment_release_order_id')
                ->constrained('allotment_release_orders')
                ->onDelete('cascade');
            $table->foreignId('appropriation_id')
                ->constrained('appropriations')
                ->onDelete('cascade');
            $table->string('account_code');
            $table->string('ppa_description')->nullable();
            $table->decimal('authorized_appropriation', 15, 2)->default(0);
            $table->decimal('for_later_release', 15, 2)->default(0);
            $table->decimal('previously_released_amount', 15, 2)->default(0);
            $table->decimal('this_release', 15, 2)->default(0);
            $table->timestamps();

            $table->index('account_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allotment_release_order_items');
    }
};
