<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use Auditable;
    use BelongsToOwner;

    protected $fillable = [
        'title',
        'report_type',
        'category_id',
        'year',
        'quarter',
        'owner_type',
        'owner_id',
        'has_draft_watermark',
        'status',
        'narrative_sections',
        'snapshot_data',
        'generated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'quarter' => 'integer',
        'has_draft_watermark' => 'boolean',
        'narrative_sections' => 'array',
        'snapshot_data' => 'array',
        'approved_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(IndicatorCategory::class, 'category_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
