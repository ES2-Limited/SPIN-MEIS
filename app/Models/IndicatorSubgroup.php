<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndicatorSubgroup extends Model
{
    use Auditable;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(IndicatorCategory::class, 'category_id');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class, 'subgroup_id')->orderBy('id');
    }
}
