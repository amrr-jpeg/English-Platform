<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'registration_code',
        'registration_code_expires_at',
        'is_registration_verified',
        'is_blocked',
        'failed_login_attempts',
        'blocked_until',
        'two_factor_code',
        'two_factor_expires_at',
        'xp',
        'level',
        'coins',
        'skin',
        'streak',
        'best_streak',
        'last_activity_date',
        'is_admin',
        'equipped_hat_id',
        'equipped_glasses_id',
        'equipped_hat',
        'equipped_accessory',
        'equipped_effect',
        'profile_background',
        'profile_frame',
        'verification_code',
        'is_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'registration_code',
        'two_factor_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'registration_code_expires_at' => 'datetime',
        'is_registration_verified' => 'boolean',
        'is_blocked' => 'boolean',
        'blocked_until' => 'datetime',
        'two_factor_expires_at' => 'datetime',
        'last_activity_date' => 'date',
        'is_admin' => 'boolean',
        'xp' => 'integer',
        'level' => 'integer',
        'coins' => 'integer',
        'streak' => 'integer',
        'best_streak' => 'integer',
    ];

    public function userExercises(): HasMany
    {
        return $this->hasMany(UserExercise::class);
    }

    public function exerciseAttempts(): HasMany
    {
        return $this->hasMany(ExerciseAttempt::class);
    }

    public function rewardLogs(): HasMany
    {
        return $this->hasMany(RewardLog::class);
    }

    public function userLessons(): HasMany
    {
        return $this->hasMany(UserLesson::class);
    }

    public function userItems(): HasMany
    {
        return $this->hasMany(UserItem::class);
    }

    public function shopItems(): HasMany
    {
        return $this->hasMany(UserShopItem::class);
    }
}
