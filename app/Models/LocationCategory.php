<?php

namespace App\Models;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Database\Eloquent\Model;

class LocationCategory extends Model
{
    protected $guarded = [];
      public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userLocation()
    {
        return $this->belongsTo(UserLocation::class);
    }
}
