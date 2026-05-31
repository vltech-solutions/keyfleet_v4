<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Filament\Notifications\Notification;

class FundType extends Model
{
    // use LogsActivity;

    protected $fillable = [
        'name',
        'icon',
        'balance',
        'company_id'
    ];

    public function payments()
    {
        return $this->hasMany(BookingPayments::class, 'fund_type_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //     ->logOnly([
    //         'name',
    //         'balance'
    //     ]);
    // }
}
