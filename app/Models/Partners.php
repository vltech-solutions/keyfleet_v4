<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partners extends Model
{
    protected $fillable = [
        'name', 'email', 'contact_number', 'address',
        'commission_type', 'commission_value','commission_base'
    ];

    public function cars()
    {
        return $this->hasMany(Car::class,'partner_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
