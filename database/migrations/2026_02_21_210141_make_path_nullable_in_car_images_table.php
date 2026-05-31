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
        Schema::table('car_images', function (Blueprint $table) {
            // We change the column to be nullable
            $table->string('path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_images', function (Blueprint $table) {
            // We revert it to NOT nullable
            // Note: This might fail if you have null values in the DB
            $table->string('path')->nullable(false)->change();
        });
    }
};