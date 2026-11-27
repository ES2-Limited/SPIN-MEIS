<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OwnerScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if ($user->owner_type === 'national') {
            return;
        }

        $table = $model->getTable();
        $builder->where("{$table}.owner_type", $user->owner_type)
            ->where("{$table}.owner_id", $user->owner_id);
    }
}
