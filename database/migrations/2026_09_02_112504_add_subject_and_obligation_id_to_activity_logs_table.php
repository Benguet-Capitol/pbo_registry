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
        Schema::table('activity_logs', function (Blueprint $table) {
            // Polymorphic reference to whatever model the event is directly about.
            $table->string('subject_type')->nullable()->after('details');
            $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            // Always populated for anything in the obligation family (Obligation itself,
            // or a PurchaseOrder/Disbursement/ObligationAdjustment belonging to it), so the
            // obligation history modal can do a single reliable lookup regardless of which
            // sub-entity changed and even after that sub-entity's own row is deleted.
            $table->unsignedBigInteger('obligation_id')->nullable()->after('subject_id');

            $table->index(['subject_type', 'subject_id']);
            $table->index('obligation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['subject_type', 'subject_id']);
            $table->dropIndex(['obligation_id']);
            $table->dropColumn(['subject_type', 'subject_id', 'obligation_id']);
        });
    }
};
