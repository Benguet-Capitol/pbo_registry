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
            // Denormalized from the appropriation at save time (same convention as
            // account_code/ppa_description/programs) — shown after the PPA Description
            // (still under that same thead column) when the ARO's office is PDF
            // (Provincial Development Fund). See AllotmentReleaseOrder::isPdfOffice().
            $table->string('project_no')->nullable()->after('office_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allotment_release_order_items', function (Blueprint $table) {
            $table->dropColumn('project_no');
        });
    }
};
