<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpactIndicator extends Model
{
    protected $fillable = [
        'dimension',
        'round',
        'treatment_value',
        'comparison_value',
        'sample_size',
        'confidence_interval',
    ];

    protected $casts = [
        'treatment_value' => 'decimal:4',
        'comparison_value' => 'decimal:4',
        'sample_size' => 'integer',
    ];
}
