<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadImport extends Model
{
    use Auditable;
    use BelongsToOwner;

    protected $fillable = [
        'filename',
        'file_hash',
        'owner_type',
        'owner_id',
        'uploaded_by',
        'status',
        'total_rows',
        'valid_rows',
        'error_rows',
        'errors',
        'committed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'committed_at' => 'datetime',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
