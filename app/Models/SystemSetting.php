<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'logo',
        'favicon',
        'copyright',
        'email',
        'phone',
        'address',
        'timezone',
        'currency',
        'meta_description',
        'meta_keywords',
        'social_facebook',
        'social_twitter',
        'social_linkedin',
        'social_instagram',
        'social_youtube',
        'social_whatsapp',
        'social_telegram',
        'social_github',
    ];


}
