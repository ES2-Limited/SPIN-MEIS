<?php

use App\Models\Indicator;
use App\Models\IndicatorCategory;
use App\Models\IndicatorSubgroup;
use App\Services\IndicatorCatalogueImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('catalogue loads with expected counts per category and validation report lists known defects', function () {
    $report = app(IndicatorCatalogueImporter::class)->import();

    expect($report['total'])->toBe(115);
    expect($report['counts_by_category'])->toBe([
        'results-framework' => 24,
        'communication-framework' => 11,
        'behaviour-change-communication' => 26,
        'grievance-mechanism-kpis' => 26,
        'gender-social-inclusion' => 28,
    ]);

    $defectTypes = collect($report['defects'])->pluck('type')->all();
    expect($defectTypes)->toContain(
        'duplicate_list_number',
        'merged_duplicate',
        'truncated_text',
        'missing_pad_lines_retained'
    );

    // Shared indicators IR-2.3, BC-12, GS-02 share the same shared_group_key
    $shared = Indicator::whereIn('code', ['IR-2.3', 'BC-12', 'GS-02'])->pluck('shared_group_key')->unique()->all();
    expect($shared)->toBe(['women_wua_leadership']);
});

test('adding a test category with two sub-groups adds a side-menu entry and tabs with no code change', function () {
    $category = IndicatorCategory::create([
        'name' => 'Environmental Safeguards',
        'slug' => 'environmental-safeguards',
        'code_prefix' => 'ES',
        'sort_order' => 99,
        'is_active' => true,
    ]);

    IndicatorSubgroup::create(['category_id' => $category->id, 'name' => 'Water Quality', 'slug' => 'water-quality', 'sort_order' => 1]);
    IndicatorSubgroup::create(['category_id' => $category->id, 'name' => 'Biodiversity', 'slug' => 'biodiversity', 'sort_order' => 2]);

    // Side menu on dashboard includes the new category
    $this->get('/')
        ->assertOk()
        ->assertSee('Environmental Safeguards');

    // Category page renders with both sub-group tabs
    $this->get('/categories/environmental-safeguards')
        ->assertOk()
        ->assertSee('Environmental Safeguards')
        ->assertSee('Water Quality')
        ->assertSee('Biodiversity');
});

test('indicators without targets show No target', function () {
    app(IndicatorCatalogueImporter::class)->import();

    $cf01 = Indicator::where('code', 'CF-01')->firstOrFail();
    expect($cf01->statusLabel(10))->toBe('No target');

    $this->get('/categories/communication-framework')
        ->assertOk()
        ->assertSee('CF-01')
        ->assertSee('No target');
});
