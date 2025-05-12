<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
       protected $fillable = [
        'user_id',
        'inbox_message',
        'rating_reminder',
        'promotions',
        'account'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
