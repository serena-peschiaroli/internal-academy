<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\RoleType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'phone', 'socials', 'avatar', 'password', 'role_id', 'first_access', 'is_active', 'temporary_password_expires_at'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'socials' => 'array',
            'password' => 'hashed',
            'first_access' => 'boolean',
            'is_active' => 'boolean',
            'temporary_password_expires_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function createdWorkshops(): HasMany
    {
        return $this->hasMany(Workshop::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function workshops(): BelongsToMany
    {
        return $this->belongsToMany(Workshop::class, 'registrations')
            ->withPivot(['status', 'waitlist_position'])
            ->withTimestamps();
    }

    public function hasRole(RoleType $role): bool
    {
        return $this->role?->key === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleType::ADMIN);
    }

    public function isEmployee(): bool
    {
        return $this->hasRole(RoleType::EMPLOYEE);
    }
}
