<?php

use App\Models\Indicator;
use App\Models\IndicatorValue;
use App\Models\State;
use App\Models\User;
use App\Services\IndicatorCatalogueImporter;
use App\Services\ReportGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(IndicatorCatalogueImporter::class)->import();
});

test('report carries draft watermark when unapproved value exists and clears after approval', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    $user = User::factory()->create(['owner_type' => 'national', 'owner_id' => null]);
    $this->actingAs($user);

    $pdo1 = Indicator::where('code', 'PDO-1')->firstOrFail();
    $val = IndicatorValue::create([
        'indicator_id' => $pdo1->id,
        'owner_type' => 'state',
        'owner_id' => $kano->id,
        'year' => 2025,
        'quarter' => 1,
        'value' => 12000,
        'status' => 'submitted',
    ]);

    $service = app(ReportGeneratorService::class);
    $draftReport = $service->generate('quarterly', 2025, 1, $user);

    expect($draftReport->has_draft_watermark)->toBeTrue();

    // Approve the value and regenerate
    $val->update(['status' => 'approved']);
    $cleanReport = $service->generate('quarterly', 2025, 1, $user);

    expect($cleanReport->has_draft_watermark)->toBeFalse();

    // Export matches snapshot table
    $csv = $service->exportCsv($cleanReport);
    expect($csv)->toContain('Code,Indicator,Unit,Baseline,Target,Actual,Status');
    expect($csv)->toContain('PDO-1');
    expect($csv)->toContain('12000');
});
