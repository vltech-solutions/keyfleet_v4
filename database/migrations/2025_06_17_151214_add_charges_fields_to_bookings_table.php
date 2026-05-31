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
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('fuel_charge', 8, 2)->default(0);
            $table->decimal('out_of_bounds', 8, 2)->default(0);
            $table->decimal('rfid', 8, 2)->default(0);
            $table->decimal('damages', 8, 2)->default(0);
            $table->decimal('carwash_fee', 8, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            //
        });
    }
};
