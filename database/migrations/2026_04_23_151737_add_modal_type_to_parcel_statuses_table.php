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
        Schema::table('parcel_statuses', function (Blueprint $row) {
            $row->string('modal_type')->default('receive')->after('is_default'); // receive, dispatch
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcel_statuses', function (Blueprint $row) {
            $row->dropColumn('modal_type');
        });
    }
};
