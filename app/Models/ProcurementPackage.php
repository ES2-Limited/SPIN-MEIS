<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;

class ProcurementPackage extends Model
{
    use Auditable;
    use BelongsToOwner;

    protected $fillable = [
        'package_reference',
        'title',
        'owner_type',
        'owner_id',
        'planned_date',
        'actual_date',
        'status',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date' => 'date',
    ];

    public function delayDays(): int
    {
        if (! $this->planned_date) {
            return 0;
        }

        $compareDate = $this->actual_date ?? now();

        return max(0, (int) $this->planned_date->diffInDays($compareDate, false));
    }
}
