<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const ADMIN = 'admin';
    public const ADMINISTRATOR = 'administrator';
    public const PERMIT_STAFF = 'permit_staff';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function allowedSlugs(): array
    {
        return [
            self::ADMIN,
            self::ADMINISTRATOR,
            self::PERMIT_STAFF,
        ];
    }

    public function scopeAllowed(Builder $query): Builder
    {
        return $query->whereIn('slug', self::allowedSlugs());
    }

    public static function permissions(): array
    {
        return [
            self::ADMIN => ['dashboard', 'building-permits', 'building-types', 'building-categories', 'permit-approvals', 'reports', 'users', 'audit-logs'],
            self::ADMINISTRATOR => ['dashboard', 'building-permits', 'building-types', 'building-categories', 'permit-approvals', 'reports', 'users', 'audit-logs'],
            self::PERMIT_STAFF => ['dashboard', 'building-permits'],
        ];
    }

    public static function permissionsFor(?string $slug): array
    {
        return self::permissions()[$slug] ?? [];
    }
}
