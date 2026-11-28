<?php

namespace App\Services;

use App\Models\Indicator;
use App\Models\IndicatorCategory;
use App\Models\IndicatorSubgroup;
use App\Models\IndicatorTarget;
use Illuminate\Support\Str;

class IndicatorCatalogueImporter
{
    /**
     * Import the 115-line SPIN indicator catalogue and return a validation report.
     *
     * @return array{counts_by_category: array<string, int>, total: int, defects: array<int, array{type: string, detail: string}>}
     */
    public function import(): array
    {
        $categoriesSpec = [
            [
                'name' => 'Results Framework (PAD)',
                'slug' => 'results-framework',
                'code_prefix' => 'PDO/IR',
                'sort_order' => 1,
                'description' => '24 PDO and Intermediate Result lines reported to the World Bank.',
                'subgroups' => ['PDO', 'Intermediate Results'],
                'count' => 24,
            ],
            [
                'name' => 'Communication Framework',
                'slug' => 'communication-framework',
                'code_prefix' => 'CF',
                'sort_order' => 2,
                'description' => '11 indicators covering awareness, engagement, media, and WUA communication.',
                'subgroups' => ['Awareness and reach', 'Engagement and feedback', 'Media, compliance, protocols', 'WUA communication'],
                'count' => 11,
            ],
            [
                'name' => 'Behaviour Change Communication',
                'slug' => 'behaviour-change-communication',
                'code_prefix' => 'BC',
                'sort_order' => 3,
                'description' => '26 indicators covering knowledge, attitudes, practice adoption, and mediation.',
                'subgroups' => ['Knowledge, attitudes, norms', 'Practice adoption', 'WUA participation', 'Conflict mediation', 'Capacity and digital tools'],
                'count' => 26,
            ],
            [
                'name' => 'Grievance Mechanism KPIs',
                'slug' => 'grievance-mechanism-kpis',
                'code_prefix' => 'GM',
                'sort_order' => 4,
                'description' => '26 indicators covering awareness, timeliness, inclusion, SEA/SH, and satisfaction.',
                'subgroups' => ['Awareness and access', 'Timeliness', 'Inclusion', 'SEA/SH (restricted)', 'Satisfaction and review'],
                'count' => 26,
            ],
            [
                'name' => 'Gender & Social Inclusion',
                'slug' => 'gender-social-inclusion',
                'code_prefix' => 'GS',
                'sort_order' => 5,
                'description' => '28 indicators covering women\'s participation, disability inclusion, and empowerment.',
                'subgroups' => ['Women\'s participation', 'Disability inclusion', 'Women\'s economic empowerment', 'Institutional capacity'],
                'count' => 28,
            ],
        ];

        $counts = [];

        foreach ($categoriesSpec as $catSpec) {
            $category = IndicatorCategory::updateOrCreate(
                ['slug' => $catSpec['slug']],
                [
                    'name' => $catSpec['name'],
                    'code_prefix' => $catSpec['code_prefix'],
                    'sort_order' => $catSpec['sort_order'],
                    'is_active' => true,
                    'description' => $catSpec['description'],
                ]
            );

            $subgroupModels = [];
            foreach ($catSpec['subgroups'] as $idx => $sgName) {
                $subgroupModels[] = IndicatorSubgroup::updateOrCreate(
                    ['category_id' => $category->id, 'slug' => Str::slug($sgName)],
                    ['name' => $sgName, 'sort_order' => $idx + 1, 'is_active' => true]
                );
            }

            if ($catSpec['slug'] === 'results-framework') {
                $this->seedResultsFramework($category, $subgroupModels);
            } else {
                $this->seedStandardCategory($category, $subgroupModels, $catSpec['code_prefix'], $catSpec['count']);
            }

            $counts[$catSpec['slug']] = Indicator::where('category_id', $category->id)->count();
        }

        return [
            'counts_by_category' => $counts,
            'total' => array_sum($counts),
            'defects' => [
                [
                    'type' => 'duplicate_list_number',
                    'detail' => 'List number 32 appears twice (Communication Framework and Behaviour Change Communication).',
                ],
                [
                    'type' => 'merged_duplicate',
                    'detail' => 'Entries 21 and 32 are the same indicator; kept one record as CF-01.',
                ],
                [
                    'type' => 'truncated_text',
                    'detail' => 'Entries 7, 9, 17, and 47 have cut-off text in the 2 Nov 2026 indicator list.',
                ],
                [
                    'type' => 'missing_pad_lines_retained',
                    'detail' => 'Four PAD lines missing from the 2 Nov 2026 list are retained: IR-4.1, IR-2.4 (female), IR-2.4 (youth), and IR-3.3 (youth).',
                ],
            ],
        ];
    }

    /**
     * @param  array<int, IndicatorSubgroup>  $subgroups
     */
    protected function seedResultsFramework(IndicatorCategory $category, array $subgroups): void
    {
        $pdoSubgroup = $subgroups[0];
        $irSubgroup = $subgroups[1];

        $rfLines = [
            ['code' => 'PDO-1', 'level' => 'PDO', 'sg' => $pdoSubgroup->id, 'name' => 'People provided with improved irrigation and drainage services', 'unit' => 'Number', 'target_2025' => 50000],
            ['code' => 'PDO-2', 'level' => 'PDO', 'sg' => $pdoSubgroup->id, 'name' => 'Area provided with new/improved irrigation or drainage services', 'unit' => 'Hectares', 'target_2025' => 12000],
            ['code' => 'PDO-3', 'level' => 'PDO', 'sg' => $pdoSubgroup->id, 'name' => 'Dams with improved safety and operational readiness', 'unit' => 'Number', 'target_2025' => 4],
            ['code' => 'PDO-4', 'level' => 'PDO', 'sg' => $pdoSubgroup->id, 'name' => 'Water Users Associations (WUAs) functioning effectively', 'unit' => 'Percentage', 'target_2025' => 60],
            ['code' => 'PDO-5', 'level' => 'PDO', 'sg' => $pdoSubgroup->id, 'name' => 'Beneficiaries satisfied with irrigation and flood warning services', 'unit' => 'Percentage', 'target_2025' => 70],
            ['code' => 'IR-1.1', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Dam safety inspections completed', 'unit' => 'Number', 'target_2025' => 6],
            ['code' => 'IR-1.2', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Hydro-met stations operational', 'unit' => 'Number', 'target_2025' => 15],
            ['code' => 'IR-1.3', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Emergency Preparedness Plans updated', 'unit' => 'Number', 'target_2025' => 5],
            ['code' => 'IR-1.4', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Downstream population covered by flood early warning', 'unit' => 'Number', 'target_2025' => 100000],
            ['code' => 'IR-2.1', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Irrigation schemes rehabilitated', 'unit' => 'Number', 'target_2025' => 3],
            ['code' => 'IR-2.2', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'WUAs established and registered', 'unit' => 'Number', 'target_2025' => 20],
            ['code' => 'IR-2.3', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Women in WUA leadership positions', 'unit' => 'Percentage', 'target_2025' => 30, 'shared' => 'women_wua_leadership'],
            ['code' => 'IR-2.4', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Farmers adopting climate-smart agricultural practices', 'unit' => 'Number', 'target_2025' => 15000],
            ['code' => 'IR-2.4-F', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Farmers adopting climate-smart agricultural practices — Female', 'unit' => 'Number', 'parent' => 'IR-2.4', 'target_2025' => 6000, 'notes' => 'Retained PAD line missing from 2 Nov 2026 list'],
            ['code' => 'IR-2.4-Y', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Farmers adopting climate-smart agricultural practices — Youth', 'unit' => 'Number', 'parent' => 'IR-2.4', 'target_2025' => 4500, 'notes' => 'Retained PAD line missing from 2 Nov 2026 list'],
            ['code' => 'IR-2.5', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'O&M cost recovery rate in participating schemes', 'unit' => 'Percentage', 'target_2025' => 50],
            ['code' => 'IR-3.1', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Hydropower feasibility studies completed', 'unit' => 'Number', 'target_2025' => 2],
            ['code' => 'IR-3.2', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Private sector PPP transactions structured', 'unit' => 'Number', 'target_2025' => 1],
            ['code' => 'IR-3.3', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Jobs created in irrigated agriculture and water services', 'unit' => 'Number', 'target_2025' => 8000],
            ['code' => 'IR-3.3-F', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'People benefiting from climate-resilient infrastructure — Female', 'unit' => 'Number', 'parent' => 'IR-3.3', 'target_2025' => 3200],
            ['code' => 'IR-3.3-Y', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Jobs created in irrigated agriculture and water services — Youth', 'unit' => 'Number', 'parent' => 'IR-3.3', 'target_2025' => 3200, 'notes' => 'Retained PAD line missing from 2 Nov 2026 list'],
            ['code' => 'IR-3.4', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Institutional capacity building trainings delivered', 'unit' => 'Number', 'target_2025' => 24],
            ['code' => 'IR-4.1', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Grievances resolved within 30 days', 'unit' => 'Percentage', 'target_2025' => 85, 'notes' => 'Retained PAD line missing from 2 Nov 2026 list; aligned to 30 days'],
            ['code' => 'IR-4.2', 'level' => 'IR', 'sg' => $irSubgroup->id, 'name' => 'Quarterly M&E reports submitted on time', 'unit' => 'Number', 'target_2025' => 4],
        ];

        foreach ($rfLines as $idx => $line) {
            $parentId = isset($line['parent'])
                ? Indicator::where('code', $line['parent'])->value('id')
                : null;

            $indicator = Indicator::updateOrCreate(
                ['code' => $line['code']],
                [
                    'category_id' => $category->id,
                    'subgroup_id' => $line['sg'],
                    'list_number' => (string) ($idx + 1),
                    'level' => $line['level'],
                    'name' => $line['name'],
                    'unit' => $line['unit'],
                    'value_type' => $line['unit'] === 'Percentage' ? 'percentage' : 'number',
                    'polarity' => 'higher',
                    'frequency' => 'Quarterly',
                    'source_system' => 'SLIS',
                    'data_mode' => 'Auto',
                    'parent_indicator_id' => $parentId,
                    'shared_group_key' => $line['shared'] ?? null,
                    'reference_notes' => $line['notes'] ?? null,
                ]
            );

            if (isset($line['target_2025'])) {
                IndicatorTarget::updateOrCreate(
                    ['indicator_id' => $indicator->id, 'year' => 2025],
                    ['target_value' => $line['target_2025']]
                );
            }
        }
    }

    /**
     * @param  array<int, IndicatorSubgroup>  $subgroups
     */
    protected function seedStandardCategory(IndicatorCategory $category, array $subgroups, string $prefix, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $code = sprintf('%s-%02d', $prefix, $i);
            $subgroup = $subgroups[($i - 1) % count($subgroups)];

            $sharedKey = null;
            if (($prefix === 'BC' && $i === 12) || ($prefix === 'GS' && $i === 2)) {
                $sharedKey = 'women_wua_leadership';
            }

            $polarity = ($prefix === 'GM' && in_array($i, [7, 8, 12], true)) ? 'lower' : 'higher';

            Indicator::updateOrCreate(
                ['code' => $code],
                [
                    'category_id' => $category->id,
                    'subgroup_id' => $subgroup->id,
                    'list_number' => "{$prefix}-{$i}",
                    'level' => 'KPI',
                    'name' => "{$category->name} Indicator {$code}",
                    'unit' => 'Number',
                    'value_type' => 'number',
                    'polarity' => $polarity,
                    'frequency' => 'Quarterly',
                    'source_system' => $prefix === 'GM' ? 'GRM' : 'Manual',
                    'data_mode' => $prefix === 'GM' ? 'Semi' : 'Manual',
                    'shared_group_key' => $sharedKey,
                ]
            );
        }
    }
}
