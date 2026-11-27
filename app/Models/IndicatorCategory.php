<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndicatorCategory extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'slug',
        'code_prefix',
        'sort_order',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subgroups(): HasMany
    {
        return $this->hasMany(IndicatorSubgroup::class, 'category_id')->orderBy('sort_order');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class, 'category_id')->orderBy('id');
    }
}
