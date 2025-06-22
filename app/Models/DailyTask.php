<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTask extends Model
{

   protected $fillable = ['user_id', 'employee_checking_id', 'task_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function descriptions()
    {
        return $this->hasMany(TaskDescription::class, 'daily_task_id');
    }
    public function employeeChecking()
{
    return $this->belongsTo(EmployeeChecking::class, 'employee_checking_id');
}
}
