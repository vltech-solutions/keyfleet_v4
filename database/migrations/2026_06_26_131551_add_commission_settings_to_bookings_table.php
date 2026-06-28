<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('commission_type')->nullable()->after('company_earnings');
            $table->decimal('commission_value', 10, 2)->nullable()->after('commission_type');
            $table->string('commission_base')->nullable()->after('commission_value');
            $table->decimal('commission_rate_applied', 10, 2)->nullable()->after('commission_base');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'commission_type',
                'commission_value',
                'commission_base',
                'commission_rate_applied'
            ]);
        });
    }
};