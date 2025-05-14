<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTask extends Model
{

protected $fillable = ['user_id', 'task_date'];

    public function descriptions()
    {
        return $this->hasMany(TaskDescription::class, 'daily_task_id');
    }

}
