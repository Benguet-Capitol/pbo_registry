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
        Schema::table('allotment_release_order_items', function (Blueprint $table) {
            // Denormalized from the appropriation's office at save time (same convention
            // as account_code/ppa_description/programs) — used to group rows under a
            // per-office header when the ARO consolidates all Special Education Fund
            // offices for one Allotment Class (see AllotmentReleaseOrder::isSefConsolidated()).
            $table->string('office_label')->nullable()->after('programs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allotment_release_order_items', function (Blueprint $table) {
            $table->dropColumn('office_label');
        });
    }
};
