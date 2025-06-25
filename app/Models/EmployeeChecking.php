<?php

namespace App\Models;

use App\Models\User;
use App\Models\Earning;
use App\Models\DailyTask;
use Illuminate\Database\Eloquent\Model;

class EmployeeChecking extends Model
{

    protected $fillable = [
        'user_id', 'role', 'current_location', 'lat', 'long', 'status', 'date',
        'check_in', 'check_out', 'total_hours', 'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyTasks()
    {
        return $this->hasMany(DailyTask::class, 'employee_checking_id');
    }

    public function earnings()
    {
        return $this->hasMany(Earning::class, 'employee_checking_id');
    }
}
