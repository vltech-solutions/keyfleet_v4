<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('fund_type_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('reservation_fee', 10, 2)->default(0);
            $table->string('reservation_fee_receipt')->nullable();
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['fund_type_id']);
            $table->dropColumn(['fund_type_id', 'reservation_fee', 'reservation_fee_receipt']);
        });
    }
};