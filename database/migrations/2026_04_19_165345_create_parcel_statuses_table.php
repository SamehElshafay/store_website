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
        Schema::create('parcel_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. جاهز, تم تسليمه
            $table->string('key')->unique(); // e.g. ready, delivered
            $table->string('color')->nullable()->default('#6366f1');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel_statuses');
    }
};
