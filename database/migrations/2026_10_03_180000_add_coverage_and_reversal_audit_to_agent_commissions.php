<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_commissions', function (Blueprint $table): void {
            $table->decimal('actual_base_subscription_amount', 14, 2)->nullable()->after('agent_program_id');
            $table->date('payment_coverage_start')->nullable()->after('actual_base_subscription_amount');
            $table->date('payment_coverage_end')->nullable()->after('payment_coverage_start');
            $table->date('eligible_coverage_start')->nullable()->after('payment_coverage_end');
            $table->date('eligible_coverage_end')->nullable()->after('eligible_coverage_start');
            $table->unsignedInteger('total_coverage_days')->nullable()->after('eligible_coverage_end');
            $table->unsignedInteger('eligible_coverage_days')->nullable()->after('total_coverage_days');
            $table->unsignedSmallInteger('commission_duration_months')->nullable()->after('commission_rate');
            $table->decimal('reversed_base_amount', 14, 2)->default(0)->after('commission_amount');
            $table->decimal('reversed_amount', 14, 2)->default(0)->after('reversed_base_amount');
        });
    }

    public function down(): void
    {
        Schema::table('agent_commissions', function (Blueprint $table): void {
            $table->dropColumn([
                'actual_base_subscription_amount',
                'payment_coverage_start',
                'payment_coverage_end',
                'eligible_coverage_start',
                'eligible_coverage_end',
                'total_coverage_days',
                'eligible_coverage_days',
                'commission_duration_months',
                'reversed_base_amount',
                'reversed_amount',
            ]);
        });
    }
};
