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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->string('renter_name');
            $table->string('contact_number')->nullable();
            $table->string('destination')->nullable();
            $table->decimal('daily_rate', 8, 2)->default(0);
            $table->decimal('days_rented', 8, 2)->default(0);
            $table->decimal('total_rent_due', 8, 2)->default(0);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('discount', 8, 2)->default(0);
            $table->decimal('total_due', 8, 2)->default(0);
            $table->decimal('paid_amount', 8, 2)->default(0);
            $table->decimal('balance', 8, 2)->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
