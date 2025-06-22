<?php

namespace App\Models;


use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @method static create(array $array)
 * @method static findOrFail(int|string|null $id)
 * @property mixed $role
 * @property mixed $avatar
 */
class User extends Authenticatable implements FilamentUser,HasAvatar
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'country_code',
        'address',
        'working_days',
        'hourly_working_rate',
        'hourly_working_rate_vat',
        'avatar',
        'avatar_url',
        'dob',
        'gender',
        'provider',
        'provider_id',
        'total_use_storage',
        'total_use_storage_limit',
        'total_sallary_amount',
        'is_verified',
        'otp',
    ];

    protected $hidden = [
        'password',
        'otp_expires_at',
        'email_verified_at',
        'reset_password_token',
        'reset_password_token_expire_at',
        'is_otp_verified',
        'created_at',
        'updated_at',
        'remember_token',
        'delete_token',
        'delete_token_expires_at',
        'otp_created_at',
        'deleted_at',
        'provider',
        'provider_id'
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
    public function getFilamentAvatarUrl(): ?string
    {
        $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar_url');
        return $this->$avatarColumn ? Storage::url($this->$avatarColumn) : null;
    }

    public function userLocations()
    {
        return $this->hasMany(UserLocation::class);
    }

    public function employeeChecking()
    {
        return $this->hasMany(EmployeeChecking::class);
    }
    public function dailyTask()
    {
        return $this->hasOne(DailyTask::class);
    }

    public function todayDurations()
    {
        return $this->hasMany(TodayDuration::class);
    }

    public function locations()
    {
        return $this->hasMany(UserLocation::class);
    }
}
