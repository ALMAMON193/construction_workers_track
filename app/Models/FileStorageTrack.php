<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileStorageTrack extends Model
{
    protected $fillable = ['user_id', 'size', 'file'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
