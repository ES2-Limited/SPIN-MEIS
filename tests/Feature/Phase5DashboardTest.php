<?php

use App\Models\Indicator;
use App\Models\IndicatorValue;
use App\Models\State;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\IndicatorCatalogueImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(IndicatorCatalogueImporter::class)->import();
});

test('scorecard counts equal indicator table counts for the same filters', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    $user = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);
    $this->actingAs($user);

    $pdo1 = Indicator::where('code', 'PDO-1')->firstOrFail();
    IndicatorValue::create([
        'indicator_id' => $pdo1->id,
        'owner_type' => 'state',
        'owner_id' => $kano->id,
        'year' => 2025,
        'quarter' => 1,
        'value' => 48000, // >= 90% of 50000 -> On track
    ]);

    $data = app(DashboardService::class)->getScorecard(['year' => 2025]);
    $rfRow = collect($data['scorecard'])->firstWhere('slug', 'results-framework');

    expect($rfRow['total'])->toBe(24);
    expect($rfRow['on_track'])->toBe(1);
    expect($rfRow['on_track'] + $rfRow['needs_attention'] + $rfRow['off_track'] + $rfRow['no_target'] + $rfRow['no_data'])->toBe($rfRow['total']);
});

test('two users in different scopes see different dashboard numbers from the same data', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    $jigawa = State::create(['name' => 'Jigawa', 'code' => 'JG']);

    $pdo1 = Indicator::where('code', 'PDO-1')->firstOrFail();
    $pdo2 = Indicator::where('code', 'PDO-2')->firstOrFail();

    IndicatorValue::create([
        'indicator_id' => $pdo1->id,
        'owner_type' => 'state',
        'owner_id' => $kano->id,
        'year' => 2025,
        'quarter' => 1,
        'value' => 48000, // On track
    ]);

    IndicatorValue::create([
        'indicator_id' => $pdo2->id,
        'owner_type' => 'state',
        'owner_id' => $jigawa->id,
        'year' => 2025,
        'quarter' => 1,
        'value' => 1000, // Off track (< 60% of 12000)
    ]);

    $kanoUser = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);
    $jigawaUser = User::factory()->create(['owner_type' => 'state', 'owner_id' => $jigawa->id]);

    $this->actingAs($kanoUser);
    $kanoData = app(DashboardService::class)->getScorecard(['year' => 2025]);
    expect($kanoData['headline']['on_track'])->toBe(1);
    expect($kanoData['headline']['off_track'])->toBe(0);

    $this->actingAs($jigawaUser);
    $jigawaData = app(DashboardService::class)->getScorecard(['year' => 2025]);
    expect($jigawaData['headline']['on_track'])->toBe(0);
    expect($jigawaData['headline']['off_track'])->toBe(1);
});
