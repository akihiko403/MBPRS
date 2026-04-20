<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuildingType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public function permits(): HasMany
    {
        return $this->hasMany(BuildingPermit::class);
    }
}
