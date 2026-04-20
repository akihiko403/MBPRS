<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class BuildingPermit extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'Pending';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_RETURNED = 'Returned';
    protected $fillable = [
        'permit_id',
        'owner_last_name',
        'owner_first_name',
        'owner_middle_name',
        'owner_suffix',
        'building_type_id',
        'building_category_id',
        'barangay',
        'city_municipality',
        'province',
        'status',
        'remarks',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_RETURNED,
        ];
    }

    public static function generatePermitId(): string
    {
        $monthPrefix = 'MBPR-'.now()->format('Ym');
        $prefix = 'MBPR-'.now()->format('Ymd');
        $latest = self::withTrashed()
            ->where(function (Builder $query) use ($monthPrefix) {
                $query->where('permit_id', 'like', $monthPrefix.'__-%')
                    ->orWhere('permit_id', 'like', $monthPrefix.'-%');
            })
            ->orderByDesc('id')
            ->value('permit_id');

        $next = 1;

        if ($latest) {
            $parts = explode('-', $latest);
            $next = ((int) end($parts)) + 1;
        }

        return sprintf('%s-%04d', $prefix, $next);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $builder, string $search) {
                $builder->where(function (Builder $nested) use ($search) {
                    $nested->where('permit_id', 'like', "%{$search}%")
                        ->orWhere('owner_last_name', 'like', "%{$search}%")
                        ->orWhere('owner_first_name', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('barangay', 'like', "%{$search}%")
                        ->orWhere('city_municipality', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhereRaw("owner_last_name || ', ' || owner_first_name LIKE ?", ["%{$search}%"])
                        ->orWhereHas('buildingType', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('buildingCategory', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $builder, string $status) => $builder->where('status', $status))
            ->when($filters['building_type_id'] ?? null, fn (Builder $builder, $type) => $builder->where('building_type_id', $type))
            ->when($filters['building_category_id'] ?? null, fn (Builder $builder, $category) => $builder->where('building_category_id', $category))
            ->when($filters['barangay'] ?? null, fn (Builder $builder, $barangay) => $builder->where('barangay', 'like', "%{$barangay}%"))
            ->when($filters['city_municipality'] ?? null, fn (Builder $builder, $cityMunicipality) => $builder->where('city_municipality', 'like', "%{$cityMunicipality}%"))
            ->when($filters['province'] ?? null, fn (Builder $builder, $province) => $builder->where('province', 'like', "%{$province}%"))
            ->when($filters['month'] ?? null, fn (Builder $builder, $month) => $builder->whereMonth('created_at', $month))
            ->when($filters['year'] ?? null, fn (Builder $builder, $year) => $builder->whereYear('created_at', $year))
            ->when($filters['date_from'] ?? null, fn (Builder $builder, $from) => $builder->whereDate('created_at', '>=', $from))
            ->when($filters['date_to'] ?? null, fn (Builder $builder, $to) => $builder->whereDate('created_at', '<=', $to));
    }

    public function buildingType(): BelongsTo
    {
        return $this->belongsTo(BuildingType::class);
    }

    public function buildingCategory(): BelongsTo
    {
        return $this->belongsTo(BuildingCategory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(PermitStatusLog::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BuildingPermitDocument::class);
    }

    public function getOwnerFullNameAttribute(): string
    {
        return trim(collect([
            $this->owner_last_name ? $this->owner_last_name.',' : null,
            $this->owner_first_name,
            $this->owner_middle_name,
            $this->owner_suffix,
        ])->filter()->implode(' '));
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->barangay,
            $this->city_municipality,
            $this->province,
        ])->filter()->implode(', ');
    }

    protected static function booted(): void
    {
        static::deleting(function (self $permit): void {
            if (! $permit->isForceDeleting()) {
                return;
            }

            $permit->documents()->get()->each(function (BuildingPermitDocument $document): void {
                Storage::disk('local')->delete($document->path);
            });
        });
    }
}
