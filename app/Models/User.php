<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'room_number',
        'specialization',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function clinicSessions(): HasMany
    {
        return $this->hasMany(ClinicSession::class, 'doctor_id');
    }

    public function queueTokens(): HasMany
    {
        return $this->hasMany(QueueToken::class, 'doctor_id');
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
