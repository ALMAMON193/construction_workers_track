<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTaskItem extends Model
{
    protected $fillable = ['daily_task_id', 'task_number', 'task_description'];

    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class);
    }

}
