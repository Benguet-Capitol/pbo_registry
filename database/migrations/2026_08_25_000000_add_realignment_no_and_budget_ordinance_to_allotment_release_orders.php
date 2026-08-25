<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('allotment_release_orders', function (Blueprint $table) {
            // Analogous to supplemental_no (SB No.) for the Supplemental Budget fund
            // source — for the new "Annual Budget (Budget Ordinance)" fund source,
            // identifies which Realignment batch (realignment_no) the ARO's released
            // amounts are drawn from.
            $table->string('realignment_no')->nullable()->after('supplemental_no');
        });

        DB::statement("ALTER TABLE allotment_release_orders MODIFY fund_source ENUM('Annual Budget', 'Annual Budget (Budget Ordinance)', 'Supplemental Budget', 'Reenacted Budget')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE allotment_release_orders MODIFY fund_source ENUM('Annual Budget', 'Supplemental Budget', 'Reenacted Budget')");

        Schema::table('allotment_release_orders', function (Blueprint $table) {
            $table->dropColumn('realignment_no');
        });
    }
};
