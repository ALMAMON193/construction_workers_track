<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTask extends Model
{

    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyTaskItems()
    {
        return $this->hasMany(DailyTaskItem::class);
    }
}
