<?php

use App\Models\ProcurementPackage;
use App\Models\Rbda;
use App\Models\Scheme;
use App\Models\State;
use App\Models\User;
use App\Services\StepImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('model 1 and model 2 views show the right owner for each scheme and respect scope', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    $rbda = Rbda::create(['name' => 'HJRBDA', 'code' => 'HJ']);

    $m1 = Scheme::create(['name' => 'Model 1 Scheme', 'code' => 'M1-1', 'state_id' => $kano->id, 'rbda_id' => $rbda->id, 'delivery_model' => 'model_1']);
    $m2 = Scheme::create(['name' => 'Model 2 Scheme', 'code' => 'M2-1', 'state_id' => $kano->id, 'rbda_id' => $rbda->id, 'delivery_model' => 'model_2']);

    expect($m1->owner_type)->toBe('rbda');
    expect((int) $m1->owner_id)->toBe($rbda->id);
    expect($m2->owner_type)->toBe('state');
    expect((int) $m2->owner_id)->toBe($kano->id);

    $kanoUser = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);
    $this->actingAs($kanoUser);

    $this->get('/project-data/river-basin')
        ->assertOk()
        ->assertSee('Model 2 Scheme')
        ->assertDontSee('Model 1 Scheme');
});

test('step file with mismatched package reference is reported, not silently dropped', function () {
    ProcurementPackage::create([
        'package_reference' => 'SPIN-CW-01',
        'title' => 'Dam Rehabilitation Works',
        'planned_date' => '2025-06-01',
        'status' => 'planned',
    ]);

    $csv = implode("\n", [
        'package_reference,actual_date,status',
        'SPIN-CW-01,2025-06-15,completed',
        'SPIN-UNKNOWN-99,2025-07-01,completed',
    ]);

    $result = app(StepImportService::class)->import($csv);

    expect($result['matched'])->toBe(1);
    expect($result['mismatched'])->toBe(['SPIN-UNKNOWN-99']);
});
