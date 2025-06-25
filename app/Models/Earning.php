<?php

namespace App\Models;

use App\Models\User;
use App\Models\EmployeeChecking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Earning extends Model
{
    protected $guarded = [];
    protected $table = 'earnings';
    protected $casts = [
        'total_hours' => 'integer',
        'total_earning' => 'float',
        'vat' => 'float',
        'net_earning' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employeeChecking(): BelongsTo
    {
        return $this->belongsTo(EmployeeChecking::class);
    }
}
