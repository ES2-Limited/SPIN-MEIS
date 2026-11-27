<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicator extends Model
{
    use Auditable;

    protected $fillable = [
        'category_id',
        'subgroup_id',
        'code',
        'list_number',
        'level',
        'name',
        'unit',
        'value_type',
        'polarity',
        'is_cumulative',
        'frequency',
        'source_system',
        'data_mode',
        'owner_role',
        'baseline_value',
        'parent_indicator_id',
        'shared_group_key',
        'disaggregation_config',
        'reference_notes',
        'is_active',
    ];

    protected $casts = [
        'is_cumulative' => 'boolean',
        'baseline_value' => 'decimal:4',
        'disaggregation_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(IndicatorCategory::class, 'category_id');
    }

    public function subgroup(): BelongsTo
    {
        return $this->belongsTo(IndicatorSubgroup::class, 'subgroup_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_indicator_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_indicator_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(IndicatorTarget::class, 'indicator_id')->orderBy('year');
    }

    public function targetForYear(?int $year = null): ?float
    {
        $year ??= (int) date('Y');
        $target = $this->targets->firstWhere('year', $year) ?? $this->targets->last();

        return $target?->target_value !== null ? (float) $target->target_value : null;
    }

    public function statusLabel(?float $actual = null, ?int $year = null): string
    {
        if ($this->value_type === 'yes_no') {
            if ($actual === null) {
                return 'No data';
            }

            return $actual >= 1.0 ? 'On track' : 'Off track';
        }

        $target = $this->targetForYear($year);

        if ($target === null) {
            return 'No target';
        }

        if ($actual === null) {
            return 'No data';
        }

        if ($target == 0.0) {
            return $actual <= 0.0 ? 'On track' : 'Off track';
        }

        $ratio = $this->polarity === 'lower'
            ? ($target / max($actual, 0.0001)) * 100
            : ($actual / $target) * 100;

        if ($ratio >= 90) {
            return 'On track';
        }

        if ($ratio >= 60) {
            return 'Needs attention';
        }

        return 'Off track';
    }
}
