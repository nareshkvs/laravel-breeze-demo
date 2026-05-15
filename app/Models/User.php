<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;


#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    // A user can apply for many leaves
    public function leaves(): HasMany
    {
        return $this->hasMany(UserLeave::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN || (is_string($this->role) && $this->role === UserRole::ADMIN->value);
    }

    public function isUser(): bool
    {
        return $this->role === UserRole::USER || (is_string($this->role) && $this->role === UserRole::USER->value);
    }
}
