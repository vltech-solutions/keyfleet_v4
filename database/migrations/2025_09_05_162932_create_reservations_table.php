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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->string('destination');
            $table->string('pickup_option');
            $table->string('pickup_address')->nullable();
            $table->string('return_address')->nullable();
            $table->boolean('with_driver')->default(false);
            $table->text('other_drivers')->nullable();
            $table->datetime('datetime_declined')->nullable();
            $table->text('decline_reason')->nullable();
            $table->foreignId('selected_car_id')->constrained('cars')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, approved, decline
            $table->foreignId('company_id')->default(1)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
