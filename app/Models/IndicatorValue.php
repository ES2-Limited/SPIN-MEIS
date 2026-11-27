<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorValue extends Model
{
    use Auditable;
    use BelongsToOwner;

    protected $fillable = [
        'indicator_id',
        'scheme_id',
        'owner_type',
        'owner_id',
        'year',
        'quarter',
        'numerator',
        'denominator',
        'value',
        'disaggregation',
        'comment',
        'evidence_path',
        'milestone_stage',
        'narrative',
        'channel',
        'source',
        'rule_version',
        'client_generated_id',
        'status',
        'submitted_by',
        'submitted_at',
        'verified_by',
        'verified_at',
        'approved_by',
        'approved_at',
        'reopen_reason',
        'reopen_requested_by',
        'has_conflict',
    ];

    protected $casts = [
        'year' => 'integer',
        'quarter' => 'integer',
        'numerator' => 'decimal:4',
        'denominator' => 'decimal:4',
        'value' => 'decimal:4',
        'disaggregation' => 'array',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'has_conflict' => 'boolean',
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(Scheme::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isLocked(): bool
    {
        return $this->status === 'approved';
    }
}
