<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyReferral extends Model
{
    protected $fillable = [
        'referrer_company_id',
        'referred_company_id',
        'is_converted',
        'reward_given',
    ];

    public function referrer()
    {
        return $this->belongsTo(Company::class, 'referrer_company_id');
    }

    public function referred()
    {
        return $this->belongsTo(Company::class, 'referred_company_id');
    }
}
