<?php

namespace App\Traits;

use App\Models\Scopes\OwnerScope;
use App\Models\User;

trait BelongsToOwner
{
    public static function bootBelongsToOwner(): void
    {
        static::addGlobalScope(new OwnerScope);
    }

    public function isOwnedBy(User $user): bool
    {
        if ($user->owner_type === 'national') {
            return true;
        }

        return $this->owner_type === $user->owner_type
            && (int) $this->owner_id === (int) $user->owner_id;
    }
}
