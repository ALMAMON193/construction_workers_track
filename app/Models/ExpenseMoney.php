<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ExpenseMoney extends Model
{
    protected $guarded = [];
     public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getFileAttribute($value): ?string
    {
        return empty($value) ? null : (filter_var($value, FILTER_VALIDATE_URL) ? $value : (request()->is('api/*') ? url($value) : $value));
    }
}
