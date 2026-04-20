<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'profile_photo_path',
        'notifications_read_at',
        'password',
        'role_id',
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
            'notifications_read_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function createdPermits(): HasMany
    {
        return $this->hasMany(BuildingPermit::class, 'created_by');
    }

    public function approvedPermits(): HasMany
    {
        return $this->hasMany(BuildingPermit::class, 'approved_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role?->slug, $roles, true);
    }

    public function canAccess(string $module): bool
    {
        return in_array($module, Role::permissionsFor($this->role?->slug), true);
    }

    public function canDeleteRecords(): bool
    {
        return $this->hasRole(Role::ADMIN, Role::ADMINISTRATOR);
    }
}
