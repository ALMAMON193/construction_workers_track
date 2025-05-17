<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function employeeChecking()
    {
        return $this->belongsTo(EmployeeChecking::class);
    }
   protected $casts = [
        'total_hours' => 'integer',
        'total_earning' => 'float',
        'vat' => 'float',
        'net_earning' => 'float',
    ];

}
