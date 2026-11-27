<?php

use App\Models\IndicatorValue;
use App\Models\State;
use App\Models\User;
use App\Services\IndicatorCatalogueImporter;
use App\Services\SpreadsheetImportService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    app(IndicatorCatalogueImporter::class)->import();
});

test('file with 3 bad rows shows 3 row-level errors in preview and saves nothing', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    $user = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);

    $csv = implode("\n", [
        'indicator_code,owner_type,owner_code,year,quarter,numerator,denominator,value,comment',
        'PDO-1,state,KN,2025,1,,,100,Valid row',
        'BAD-CODE,state,KN,2025,1,,,100,Bad indicator',
        'PDO-1,state,KN,2025,9,,,100,Bad quarter',
        'PDO-1,state,KN,2025,2,,,not-a-number,Bad value',
    ]);

    $service = app(SpreadsheetImportService::class);
    $preview = $service->validateAndPreview($csv, $user, 'bad_upload.csv');

    expect($preview['import']->error_rows)->toBe(3);
    expect($preview['errors'])->toHaveCount(3);
    expect(IndicatorValue::withoutGlobalScopes()->count())->toBe(0);

    expect(fn () => $service->commit($preview['import'], $preview['valid_rows'], $user))
        ->toThrow(InvalidArgumentException::class);
    expect(IndicatorValue::withoutGlobalScopes()->count())->toBe(0);
});

test('state user file containing another state rows is rejected for those rows', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    State::create(['name' => 'Jigawa', 'code' => 'JG']);

    $kanoUser = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);

    $csv = implode("\n", [
        'indicator_code,owner_type,owner_code,year,quarter,numerator,denominator,value,comment',
        'PDO-1,state,KN,2025,1,,,100,Own state',
        'PDO-1,state,JG,2025,1,,,200,Other state',
    ]);

    $service = app(SpreadsheetImportService::class);
    $preview = $service->validateAndPreview($csv, $kanoUser, 'cross_state.csv');

    expect($preview['valid_rows'])->toHaveCount(1);
    expect($preview['errors'])->toHaveCount(1);
    expect($preview['errors'][0]['message'])->toContain('another owner scope');
});

test('re-uploading the same file does not create duplicates', function () {
    $kano = State::create(['name' => 'Kano', 'code' => 'KN']);
    $user = User::factory()->create(['owner_type' => 'state', 'owner_id' => $kano->id]);

    $csv = implode("\n", [
        'indicator_code,owner_type,owner_code,year,quarter,numerator,denominator,value,comment',
        'PDO-1,state,KN,2025,1,,,150,First upload',
    ]);

    $service = app(SpreadsheetImportService::class);

    $first = $service->validateAndPreview($csv, $user, 'kano_q1.csv');
    $inserted1 = $service->commit($first['import'], $first['valid_rows'], $user);
    expect($inserted1)->toBe(1);

    $second = $service->validateAndPreview($csv, $user, 'kano_q1.csv');
    $inserted2 = $service->commit($second['import'], $second['valid_rows'], $user);
    expect($inserted2)->toBe(0);

    expect(IndicatorValue::withoutGlobalScopes()->count())->toBe(1);
});
