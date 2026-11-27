<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scheme extends Model
{
    use Auditable;
    use BelongsToOwner;

    protected $fillable = [
        'name',
        'code',
        'state_id',
        'rbda_id',
        'river_basin_id',
        'delivery_model',
        'owner_type',
        'owner_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Scheme $scheme) {
            if ($scheme->delivery_model === 'model_1') {
                $scheme->owner_type = 'rbda';
                $scheme->owner_id = $scheme->rbda_id;
            } elseif ($scheme->delivery_model === 'model_2') {
                $scheme->owner_type = 'state';
                $scheme->owner_id = $scheme->state_id;
            }
        });
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function rbda(): BelongsTo
    {
        return $this->belongsTo(Rbda::class);
    }

    public function riverBasin(): BelongsTo
    {
        return $this->belongsTo(RiverBasin::class);
    }
}
