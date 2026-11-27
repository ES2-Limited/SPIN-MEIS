<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Auditable;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'owner_type',
        'owner_id',
        'status',
        'invited_at',
        'activated_at',
        'disabled_at',
        'last_login_at',
        'last_access_review_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'invited_at' => 'datetime',
            'activated_at' => 'datetime',
            'disabled_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_access_review_at' => 'datetime',
        ];
    }

    public function isNational(): bool
    {
        return $this->owner_type === 'national';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
