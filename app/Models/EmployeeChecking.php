<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeChecking extends Model
{
    protected $guarded = [];
     public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class);
    }
}
