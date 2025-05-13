<?php

namespace App\Models;


use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @method static create(array $array)
 * @method static findOrFail(int|string|null $id)
 * @property mixed $role
 * @property mixed $avatar
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens,SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'reset_password_token',
        'reset_password_token_expire_at',
        'is_otp_verified',
        'created_at',
        'updated_at',
        'role',
        'status',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'is_otp_verified' => 'boolean',
            'reset_password_token_expires_at' => 'datetime',
            'password' => 'hashed'
        ];
    }
    public function getAvatarAttribute($value): ?string
    {
        return empty($value) ? null : (filter_var($value, FILTER_VALIDATE_URL) ? $value : (request()->is('api/*') ? url($value) : $value));
    }
    public function notificationSettings()
    {
        return $this->hasOne(NotificationSetting::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function fileStorageTracks()
    {
        return $this->hasMany(FileStorageTrack::class);
    }

    public function userLocations()
    {
        return $this->hasMany(UserLocation::class);
    }

    public function employeeChecking()
    {
        return $this->hasMany(EmployeeChecking::class);
    }

    public function expenseMoney()
    {
        return $this->hasMany(ExpenseMoney::class);
    }
    public function dailyTask()
    {
        return $this->hasOne(DailyTask::class);
    }

    public function facingProblems()
    {
        return $this->hasMany(FacingProblem::class);
    }

    public function todayDurations()
    {
        return $this->hasMany(TodayDuration::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}
