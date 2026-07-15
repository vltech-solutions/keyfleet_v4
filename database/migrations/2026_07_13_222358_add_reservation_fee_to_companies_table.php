<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('reservation_fee_amount', 10, 2)->default(0)->after('offer_driver_service');
            $table->boolean('is_reservation_fee_enabled')->default(false)->after('reservation_fee_amount');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['reservation_fee_amount', 'is_reservation_fee_enabled']);
        });
    }
};