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
            // Editable per row so a single ARO can group its account codes under
            // multiple PPA Codes (each with its own Subtotal), not just the one
            // PPA Code prefilled from the office at the ARO header level.
            $table->string('ppa_code')->nullable()->after('appropriation_id');
            // Snapshot of appropriations.programs at save time, so the printed
            // "Program" header row above a group of accounts stays accurate even
            // if the source appropriation's programs value changes later.
            $table->string('programs')->nullable()->after('ppa_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allotment_release_order_items', function (Blueprint $table) {
            $table->dropColumn(['ppa_code', 'programs']);
        });
    }
};
