<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rbda extends Model
{
    use Auditable;

    protected $fillable = ['name', 'code', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function schemes(): HasMany
    {
        return $this->hasMany(Scheme::class);
    }

    public function riverBasins(): HasMany
    {
        return $this->hasMany(RiverBasin::class);
    }
}
