<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class BookingPayments extends Model
{
    protected $fillable = [
        'booking_id',
        'fund_type_id',
        'amount',
        'payment_date',
        'payment_notes',
        'company_id',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function fundType()
    {
        return $this->belongsTo(FundType::class, 'fund_type_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    protected static function booted()
    {
        static::creating(function ($payment) {
            if (Filament::getTenant()) {
                $payment->company_id = Filament::getTenant()->id;
            }
        });

        static::created(function ($payment) {
            $payment->recalculateFundBalance();
        });

        static::updated(function ($payment) {
            $payment->recalculateFundBalanceOnFundChange();
        });

        static::deleted(function ($payment) {
            $payment->recalculateFundBalance();
        });
    }

    /**
     * Recalculate the fund balance for the payment's current fund.
     */
    public function recalculateFundBalance()
    {
        $fund = $this->fundType;
        if ($fund) {
            $fund->balance = $fund->payments()->sum('amount') - $fund->expenses()->sum('amount');
            $fund->save();
        }
    }

    /**
     * Handle recalculation on update, especially if fund_type changed.
     */
    public function recalculateFundBalanceOnFundChange()
    {
        $originalFundId = $this->getOriginal('fund_type_id');

        // If fund type changed, recalc both old and new funds
        if ($originalFundId && $originalFundId != $this->fund_type_id) {
            $oldFund = FundType::find($originalFundId);
            if ($oldFund) {
                $oldFund->balance = $oldFund->payments()->sum('amount') - $oldFund->expenses()->sum('amount');
                $oldFund->save();
            }

            $newFund = $this->fundType;
            if ($newFund) {
                $newFund->balance = $newFund->payments()->sum('amount') - $newFund->expenses()->sum('amount');
                $newFund->save();
            }
        } else {
            // Fund type didn't change, just recalc current fund
            $this->recalculateFundBalance();
        }
    }
}
