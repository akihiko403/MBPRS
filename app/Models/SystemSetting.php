<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
    protected $fillable = [
        'system_name',
        'system_description',
        'system_logo_path',
    ];

    public static function defaults(): array
    {
        return [
            'system_name' => 'Municipal Building Permit Repository System',
            'system_description' => 'Securely manage building permit encoding, approvals, reporting, and records access in one municipal repository.',
            'system_logo_path' => null,
        ];
    }

    public static function current(): self
    {
        if (! Schema::hasTable('system_settings')) {
            return new self(self::defaults());
        }

        return self::query()->firstOrCreate(['id' => 1], self::defaults());
    }
}
