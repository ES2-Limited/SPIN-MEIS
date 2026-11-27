<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiverBasin extends Model
{
    use Auditable;

    protected $fillable = ['name', 'code', 'rbda_id'];

    public function rbda(): BelongsTo
    {
        return $this->belongsTo(Rbda::class);
    }

    public function schemes(): HasMany
    {
        return $this->hasMany(Scheme::class);
    }
}
