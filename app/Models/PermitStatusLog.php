<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitStatusLog extends Model
{
    protected $fillable = [
        'building_permit_id',
        'old_status',
        'new_status',
        'remarks',
        'acted_by',
    ];

    public function permit(): BelongsTo
    {
        return $this->belongsTo(BuildingPermit::class, 'building_permit_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
