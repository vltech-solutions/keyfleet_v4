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
        Schema::create('customer_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('requirement_type'); // e.g. primary_id, secondary_id, proof_of_billing
            $table->string('path');             // file path or storage path
            $table->string('status')->default('pending'); // pending, approved, decline
            $table->date('date_uploaded')->nullable();
            $table->date('expiration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_requirements');
    }
};
