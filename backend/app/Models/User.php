<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'position',
        'no_hp',
        'alamat',
        'ktp',
        'kk',
        'sertifikat',
        'foto',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'sertifikat' => 'array',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Super Admin always has access. Others must be marked as 'Aktif'.
        return $this->role === 'Super Admin' || $this->status === 'Aktif';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'Super Admin';
    }

    public function isPengurus(): bool
    {
        return $this->role === 'Pengurus';
    }
}
