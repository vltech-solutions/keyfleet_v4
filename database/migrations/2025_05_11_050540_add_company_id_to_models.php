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
            $table->foreignId('company_id')->default(1)->constrained();
        });

        Schema::table('cars', function (Blueprint $table) {
            $table->foreignId('company_id')->default(1)->constrained();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('company_id')->default(1)->constrained();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('company_id')->default(1)->constrained();
        });

        Schema::table('fund_types', function (Blueprint $table) {
            $table->foreignId('company_id')->default(1)->constrained();
        });
    }
    
};
