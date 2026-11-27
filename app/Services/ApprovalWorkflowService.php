<?php

namespace App\Services;

use App\Models\IndicatorValue;
use App\Models\User;
use App\Policies\OwnerScopedPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;

class ApprovalWorkflowService
{
    public function __construct(protected OwnerScopedPolicy $policy) {}

    public function submit(IndicatorValue $record, User $actor): IndicatorValue
    {
        if ($record->isLocked()) {
            throw new InvalidArgumentException('Approved records are locked.');
        }

        if (! $this->policy->updateRecord($actor, $record)) {
            throw new AuthorizationException('You are not authorized to submit this record.');
        }

        $record->update([
            'status' => 'submitted',
            'submitted_by' => $actor->id,
            'submitted_at' => now(),
        ]);

        return $record->fresh();
    }

    public function verify(IndicatorValue $record, User $actor): IndicatorValue
    {
        if ($record->status !== 'submitted') {
            throw new InvalidArgumentException('Only submitted records can be verified.');
        }

        if (! $this->policy->verify($actor, $record)) {
            throw new AuthorizationException('You are not authorized to verify this record.');
        }

        $record->update([
            'status' => 'verified',
            'verified_by' => $actor->id,
            'verified_at' => now(),
        ]);

        return $record->fresh();
    }

    public function approve(IndicatorValue $record, User $actor): IndicatorValue
    {
        if ($record->status !== 'verified') {
            throw new InvalidArgumentException('Only verified records can be approved.');
        }

        if (! $this->policy->approve($actor, $record)) {
            throw new AuthorizationException('You are not authorized to approve this record.');
        }

        $record->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        return $record->fresh();
    }

    public function requestReopen(IndicatorValue $record, User $actor, string $reason): IndicatorValue
    {
        if ($record->status !== 'approved') {
            throw new InvalidArgumentException('Only approved records can be reopened.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('A reason is required to reopen an approved record.');
        }

        $record->update([
            'status' => 'reopen_requested',
            'reopen_reason' => $reason,
            'reopen_requested_by' => $actor->id,
        ]);

        return $record->fresh();
    }

    /**
     * Sync an offline-captured IndicatorValue idempotently.
     *
     * @param  array<string, mixed>  $payload
     * @return array{status: string, record: IndicatorValue}
     */
    public function syncOffline(array $payload, User $actor): array
    {
        $clientId = $payload['client_generated_id'] ?? null;

        if ($clientId) {
            $existing = IndicatorValue::withoutGlobalScopes()
                ->where('client_generated_id', $clientId)
                ->first();

            if ($existing) {
                if ($existing->isLocked()) {
                    return ['status' => 'rejected_locked', 'record' => $existing];
                }

                return ['status' => 'deduplicated', 'record' => $existing];
            }
        }

        // Check if an approved record already exists for the same indicator/owner/period
        $approvedMatch = IndicatorValue::withoutGlobalScopes()
            ->where('indicator_id', $payload['indicator_id'])
            ->where('owner_type', $payload['owner_type'])
            ->where('owner_id', $payload['owner_id'] ?? null)
            ->where('year', $payload['year'])
            ->where('quarter', $payload['quarter'])
            ->where('status', 'approved')
            ->first();

        if ($approvedMatch) {
            return ['status' => 'rejected_locked', 'record' => $approvedMatch];
        }

        $record = IndicatorValue::create([
            'indicator_id' => $payload['indicator_id'],
            'scheme_id' => $payload['scheme_id'] ?? null,
            'owner_type' => $payload['owner_type'],
            'owner_id' => $payload['owner_id'] ?? null,
            'year' => $payload['year'],
            'quarter' => $payload['quarter'],
            'numerator' => $payload['numerator'] ?? null,
            'denominator' => $payload['denominator'] ?? null,
            'value' => $payload['value'] ?? null,
            'comment' => $payload['comment'] ?? null,
            'channel' => 'manual',
            'source' => 'offline_sync',
            'client_generated_id' => $clientId,
            'status' => 'draft',
        ]);

        return ['status' => 'created', 'record' => $record];
    }
}
