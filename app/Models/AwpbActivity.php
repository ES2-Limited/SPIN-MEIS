<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AwpbActivity extends Model
{
    use Auditable;
    use BelongsToOwner;

    protected $fillable = [
        'activity_code',
        'component',
        'title',
        'owner_type',
        'owner_id',
        'indicator_id',
        'budget_amount',
        'expenditure_to_date',
        'year',
    ];

    protected $casts = [
        'budget_amount' => 'decimal:2',
        'expenditure_to_date' => 'decimal:2',
        'year' => 'integer',
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }

    public function absorptionRate(): float
    {
        if ((float) $this->budget_amount <= 0.0) {
            return 0.0;
        }

        return round(((float) $this->expenditure_to_date / (float) $this->budget_amount) * 100, 2);
    }
}
