<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;

class RegisterEntry extends Model
{
    use Auditable;
    use BelongsToOwner;

    protected $fillable = [
        'register_type',
        'owner_type',
        'owner_id',
        'activity_date',
        'title',
        'payload',
        'evidence_path',
        'is_bridge',
        'bridge_sunset_at',
        'client_generated_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'payload' => 'array',
        'is_bridge' => 'boolean',
        'bridge_sunset_at' => 'date',
    ];
}
