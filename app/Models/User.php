<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = ['name', 'email', 'password', 'wallet', 'balance', 'tel',];
    protected $hidden = [
        'password',
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
            'password' => 'hashed',
        ];
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    /**
     * Get all of the releases.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function releases()
    {
        return $this->hasMany(Release::class);
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }
}
