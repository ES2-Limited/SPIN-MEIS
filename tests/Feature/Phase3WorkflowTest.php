<?php

use App\Models\Indicator;
use App\Models\IndicatorValue;
use App\Models\Rbda;
use App\Models\Scheme;
use App\Models\State;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use App\Services\IndicatorCatalogueImporter;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    app(IndicatorCatalogueImporter::class)->import();
});

test('full submit, verify, approve cycle works for model 1 and model 2 with different verifiers', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    $rbda = Rbda::create(['name' => 'HJRBDA', 'code' => 'HJ']);

    $m1Scheme = Scheme::create(['name' => 'M1', 'code' => 'M1', 'state_id' => $kano->id, 'rbda_id' => $rbda->id, 'delivery_model' => 'model_1']);
    $m2Scheme = Scheme::create(['name' => 'M2', 'code' => 'M2', 'state_id' => $kano->id, 'rbda_id' => $rbda->id, 'delivery_model' => 'model_2']);

    $rbdaSubmitter = User::factory()->create(['owner_type' => 'rbda', 'owner_id' => $rbda->id]);
    $rbdaSubmitter->assignRole('RBDA data manager');
    $rbdaVerifier = User::factory()->create(['owner_type' => 'rbda', 'owner_id' => $rbda->id]);
    $rbdaVerifier->assignRole('RBDA data manager');

    $stateSubmitter = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);
    $stateSubmitter->assignRole('SPIU M&E Officer');
    $stateVerifier = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);
    $stateVerifier->assignRole('SPIU M&E Officer');

    $approver = User::factory()->create(['owner_type' => 'national', 'owner_id' => null]);
    $approver->assignRole('FPMU M&E Specialist');

    $indicator = Indicator::where('code', 'PDO-1')->firstOrFail();
    $workflow = app(ApprovalWorkflowService::class);

    // Model 1 cycle
    $v1 = IndicatorValue::create([
        'indicator_id' => $indicator->id,
        'scheme_id' => $m1Scheme->id,
        'owner_type' => $m1Scheme->owner_type,
        'owner_id' => $m1Scheme->owner_id,
        'year' => 2025,
        'quarter' => 1,
        'value' => 1000,
    ]);
    $v1 = $workflow->submit($v1, $rbdaSubmitter);
    $v1 = $workflow->verify($v1, $rbdaVerifier);
    $v1 = $workflow->approve($v1, $approver);
    expect($v1->status)->toBe('approved');

    // Model 2 cycle
    $v2 = IndicatorValue::create([
        'indicator_id' => $indicator->id,
        'scheme_id' => $m2Scheme->id,
        'owner_type' => $m2Scheme->owner_type,
        'owner_id' => $m2Scheme->owner_id,
        'year' => 2025,
        'quarter' => 1,
        'value' => 500,
    ]);
    $v2 = $workflow->submit($v2, $stateSubmitter);
    $v2 = $workflow->verify($v2, $stateVerifier);
    $v2 = $workflow->approve($v2, $approver);
    expect($v2->status)->toBe('approved');
});

test('offline sync deduplicates by client_generated_id and rejects edits to approved records', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    $user = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);
    $user->assignRole('SPIU M&E Officer');
    $this->actingAs($user);

    $indicator = Indicator::where('code', 'PDO-1')->firstOrFail();

    $payload = [
        'indicator_id' => $indicator->id,
        'owner_type' => 'state',
        'owner_id' => $kano->id,
        'year' => 2025,
        'quarter' => 2,
        'value' => 250,
        'client_generated_id' => 'offline-uuid-001',
    ];

    $this->postJson('/api/v1/offline-sync', $payload)
        ->assertOk()
        ->assertJsonPath('sync_status', 'created');

    // Re-sync same client_generated_id -> deduplicated
    $this->postJson('/api/v1/offline-sync', $payload)
        ->assertOk()
        ->assertJsonPath('sync_status', 'deduplicated');

    expect(IndicatorValue::withoutGlobalScopes()->where('client_generated_id', 'offline-uuid-001')->count())->toBe(1);

    // Approve the record, then attempt another offline sync for the same period -> rejected_locked (409)
    IndicatorValue::withoutGlobalScopes()->where('client_generated_id', 'offline-uuid-001')->update(['status' => 'approved']);

    $payload['client_generated_id'] = 'offline-uuid-002';
    $this->postJson('/api/v1/offline-sync', $payload)
        ->assertStatus(409)
        ->assertJsonPath('sync_status', 'rejected_locked');
});

test('status engine computes higher-is-better, lower-is-better, yes-no, and no-target correctly', function () {
    // Higher is better (PDO-1 target 2025 = 50000)
    $higher = Indicator::where('code', 'PDO-1')->firstOrFail();
    expect($higher->statusLabel(45000, 2025))->toBe('On track');
    expect($higher->statusLabel(35000, 2025))->toBe('Needs attention');
    expect($higher->statusLabel(20000, 2025))->toBe('Off track');

    // Lower is better (GM-07 with target 10)
    $lower = Indicator::where('code', 'GM-07')->firstOrFail();
    $lower->targets()->create(['year' => 2025, 'target_value' => 10]);
    $lower->load('targets');
    expect($lower->statusLabel(10, 2025))->toBe('On track');
    expect($lower->statusLabel(15, 2025))->toBe('Needs attention');
    expect($lower->statusLabel(25, 2025))->toBe('Off track');

    // Yes/No
    $yesNo = new Indicator(['value_type' => 'yes_no']);
    expect($yesNo->statusLabel(1))->toBe('On track');
    expect($yesNo->statusLabel(0))->toBe('Off track');

    // No target (CF-01)
    $noTarget = Indicator::where('code', 'CF-01')->firstOrFail();
    expect($noTarget->statusLabel(100, 2025))->toBe('No target');
});
