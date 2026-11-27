<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorTarget extends Model
{
    protected $fillable = [
        'indicator_id',
        'year',
        'target_value',
    ];

    protected $casts = [
        'year' => 'integer',
        'target_value' => 'decimal:4',
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class, 'indicator_id');
    }
}
