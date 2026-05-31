<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_inspections', function (Blueprint $table) {
            // Stores the S3 path: inspections/{id}/signatures/sig_123.png
            $table->string('customer_signature')->nullable()->after('functions');
            
            // Useful for general summary (e.g., "Car is clean but fuel is low")
            $table->text('general_notes')->nullable()->after('customer_signature');
            
            // Optional: Store the name of the person who actually signed
            $table->string('signee_name')->nullable()->after('general_notes');
        });
    }

    public function down(): void
    {
        Schema::table('booking_inspections', function (Blueprint $table) {
            $table->dropColumn(['customer_signature', 'general_notes', 'signee_name']);
        });
    }
};