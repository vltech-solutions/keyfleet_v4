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
        Schema::table('booking_inspections', function (Blueprint $table) {
            // Adding the JSON columns
            $table->json('tires')->nullable()->after('gas');
            $table->json('functions')->nullable()->after('tires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_inspections', function (Blueprint $table) {
            $table->dropColumn(['tires', 'functions']);
        });
    }
};