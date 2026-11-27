<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OwnerScopedPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view');
    }

    public function viewRecord(User $user, Model $record): bool
    {
        if (! $user->can('view')) {
            return false;
        }

        return $this->withinScope($user, $record);
    }

    public function createRecord(User $user): bool
    {
        if ($user->hasRole('World Bank')) {
            return false;
        }

        return $user->can('enter') || $user->can('manage indicators') || $user->can('manage users');
    }

    public function updateRecord(User $user, Model $record): bool
    {
        if ($user->hasRole('World Bank')) {
            return false;
        }

        return $user->can('enter') && $this->withinScope($user, $record);
    }

    public function deleteRecord(User $user, Model $record): bool
    {
        if ($user->hasRole('World Bank')) {
            return false;
        }

        return $user->can('manage users') && $this->withinScope($user, $record);
    }

    /**
     * Separation of duties: submitter cannot verify the same record.
     */
    public function verify(User $user, Model $record): bool
    {
        if ($user->hasRole('World Bank') || ! $user->can('verify')) {
            return false;
        }

        if (! $this->withinScope($user, $record)) {
            return false;
        }

        $submittedBy = $record->getAttribute('submitted_by');

        return $submittedBy === null || (int) $submittedBy !== (int) $user->id;
    }

    /**
     * Separation of duties: neither submitter nor verifier can approve the same record.
     */
    public function approve(User $user, Model $record): bool
    {
        if ($user->hasRole('World Bank') || ! $user->can('approve')) {
            return false;
        }

        $submittedBy = $record->getAttribute('submitted_by');
        $verifiedBy = $record->getAttribute('verified_by');

        if ($submittedBy !== null && (int) $submittedBy === (int) $user->id) {
            return false;
        }

        if ($verifiedBy !== null && (int) $verifiedBy === (int) $user->id) {
            return false;
        }

        return true;
    }

    protected function withinScope(User $user, Model $record): bool
    {
        if ($user->owner_type === 'national') {
            return true;
        }

        return $record->getAttribute('owner_type') === $user->owner_type
            && (int) $record->getAttribute('owner_id') === (int) $user->owner_id;
    }
}
