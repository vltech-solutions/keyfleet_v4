<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_programs', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->decimal('commission_rate', 7, 4);
            $table->unsignedSmallInteger('commission_duration_months');
            $table->unsignedSmallInteger('holding_period_days');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('agents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('agent_program_id')->constrained()->restrictOnDelete();
            $table->string('referral_code')->unique();
            $table->string('status')->default('pending')->index();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('agreement_accepted_at')->nullable();
            $table->string('payout_method')->nullable();
            $table->text('payout_details')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('company_referrals', function (Blueprint $table): void {
            $table->foreignId('referrer_company_id')->nullable()->change();
            $table->foreignId('referrer_user_id')->nullable()->after('referrer_company_id')->constrained('users')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->after('referrer_user_id')->constrained()->restrictOnDelete();
            $table->foreignId('agent_program_id')->nullable()->after('agent_id')->constrained()->restrictOnDelete();
            $table->string('referral_code')->nullable()->after('agent_program_id');
            $table->string('program_type')->default('customer_referral')->after('referral_code')->index();
            $table->timestamp('referred_at')->nullable()->after('program_type');
            $table->timestamp('qualified_at')->nullable()->after('referred_at');
            $table->timestamp('commission_started_at')->nullable()->after('qualified_at');
            $table->timestamp('commission_ends_at')->nullable()->after('commission_started_at');
            $table->unsignedSmallInteger('commission_duration_months')->nullable()->after('commission_ends_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unique('referred_company_id');
        });

        DB::table('company_referrals')->whereNull('referred_at')->update([
            'referred_at' => DB::raw('created_at'),
            'program_type' => 'customer_referral',
        ]);

        Schema::create('agent_commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_id')->constrained()->restrictOnDelete();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('company_referral_id')->constrained()->restrictOnDelete();
            $table->foreignId('subscription_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('agent_program_id')->constrained()->restrictOnDelete();
            $table->decimal('commissionable_amount', 14, 2);
            $table->decimal('commission_rate', 7, 4);
            $table->decimal('commission_amount', 14, 2);
            $table->unsignedSmallInteger('holding_period_days');
            $table->string('status')->default('pending')->index();
            $table->timestamp('earned_at');
            $table->timestamp('payable_at')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('agent_payouts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_id')->constrained()->restrictOnDelete();
            $table->string('reference_number')->unique();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('status')->default('draft')->index();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('agent_payout_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_payout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_commission_id')->unique()->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_payout_items');
        Schema::dropIfExists('agent_payouts');
        Schema::dropIfExists('agent_commissions');
        Schema::table('company_referrals', function (Blueprint $table): void {
            $table->dropUnique(['referred_company_id']);
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['commission_duration_months', 'commission_ends_at', 'commission_started_at', 'qualified_at', 'referred_at', 'program_type', 'referral_code']);
            $table->dropConstrainedForeignId('agent_program_id');
            $table->dropConstrainedForeignId('agent_id');
            $table->dropConstrainedForeignId('referrer_user_id');
        });
        Schema::dropIfExists('agents');
        Schema::dropIfExists('agent_programs');
    }
};
