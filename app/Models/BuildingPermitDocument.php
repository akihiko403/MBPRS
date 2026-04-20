<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingPermitDocument extends Model
{
    protected $fillable = [
        'building_permit_id',
        'original_name',
        'stored_name',
        'path',
        'mime_type',
        'size',
    ];

    public function buildingPermit(): BelongsTo
    {
        return $this->belongsTo(BuildingPermit::class);
    }
}
