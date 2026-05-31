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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('voucher_code')->nullable()->after('voucher_id');
            $table->decimal('refund_amount', 10, 2)->default(0)->after('voucher_code');
            $table->decimal('processing_fee', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('subtotal', 10, 2)->default(0)->after('processing_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
        });
    }
};
