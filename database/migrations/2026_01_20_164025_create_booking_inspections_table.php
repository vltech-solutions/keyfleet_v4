<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_inspections', function (Blueprint $table) {
            $table->id();
            // I-link natin sa booking
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            
            // Inspection Type: 'pickup' or 'return'
            $table->string('type'); 
            
            // New Columns na hiningi mo
            $table->integer('odo')->nullable()->comment('Odometer reading');
            $table->decimal('autosweep', 10, 2)->nullable()->comment('Balance/Amount');
            $table->decimal('easytrip', 10, 2)->nullable()->comment('Balance/Amount');
            $table->integer('gas')->nullable()->comment('Fuel level percentage (0-100)');
            
            // Additional info
            $table->string('inspected_by')->nullable();
            $table->text('client_signature')->nullable();
            $table->text('general_remarks')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_inspections');
    }
};