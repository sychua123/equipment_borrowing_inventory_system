<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    // ===== Module 4 (Lai Chee Kit) =====
    // The User model belongs to Module 1. Module 4 needs only the three methods below.

    // A user has many tickets
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // The bell notifications of this user (newest first)
    public function ticketNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    // Admin = Inventory Officer
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
