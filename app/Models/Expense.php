<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'car_id',
        'expense_description',
        'date',
        'amount',
        'deduct_to_fund',
        'other_payment_type',
        'fund_id',
        'company_id',
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fundType()
    {
        return $this->belongsTo(FundType::class, 'fund_type_id');
    }

    protected static function booted()
    {
        static::creating(function ($expense) {
            if (Filament::getTenant()) {
                $expense->company_id = Filament::getTenant()->id;
            }
        });

        static::created(function ($expense) {
            $expense->recalculateFundBalance();
        });

        static::updated(function ($expense) {
            $expense->recalculateFundBalanceOnFundChange();
        });

        static::deleted(function ($expense) {
            $expense->recalculateFundBalance();
        });
    }

    /**
     * Recalculate the fund balance for the expense's current fund.
     */
    public function recalculateFundBalance()
    {
        if (! $this->deduct_to_fund) {
            return;
        }

        $fund = $this->fundType;
        if ($fund) {
            $fund->balance = $fund->payments()->sum('amount') - $fund->expenses()->sum('amount');
            $fund->save();
        }
    }

    /**
     * Handle recalculation on update, especially if fund changed.
     */
    public function recalculateFundBalanceOnFundChange()
    {
        $originalFundId = $this->getOriginal('fund_type_id');
        $originalDeduct = $this->getOriginal('deduct_to_fund');

        // If deduct_to_fund was turned off
        if ($originalDeduct && !$this->deduct_to_fund && $originalFundId) {
            $fund = FundType::find($originalFundId);
            if ($fund) {
                $fund->balance = $fund->payments()->sum('amount') - $fund->expenses()->where('deduct_to_fund', true)->sum('amount');
                $fund->save();
            }

            // Optionally clear the fund_type_id if no longer linked
            $this->fund_type_id = null;
            $this->saveQuietly();
            return;
        }

        // If fund changed, recalc both old and new
        if ($originalFundId && $originalFundId != $this->fund_type_id) {
            $oldFund = FundType::find($originalFundId);
            if ($oldFund) {
                $oldFund->balance = $oldFund->payments()->sum('amount') - $oldFund->expenses()->where('deduct_to_fund', true)->sum('amount');
                $oldFund->save();
            }

            $newFund = $this->fundType;
            if ($newFund) {
                $newFund->balance = $newFund->payments()->sum('amount') - $newFund->expenses()->where('deduct_to_fund', true)->sum('amount');
                $newFund->save();
            }
        } else {
            // No change in fund or deduct toggle, just recalc current
            $this->recalculateFundBalance();
        }
    }
}
