<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskDescription extends Model
{
 protected $fillable = ['daily_task_id', 'task_name', 'description'];

    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class, 'daily_task_id');
    }
}
