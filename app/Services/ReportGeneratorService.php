<?php

namespace App\Services;

use App\Models\IndicatorCategory;
use App\Models\IndicatorValue;
use App\Models\Report;
use App\Models\User;

class ReportGeneratorService
{
    public function __construct(protected DashboardService $dashboard) {}

    public function generate(
        string $reportType,
        int $year,
        ?int $quarter,
        User $actor,
        ?int $categoryId = null,
    ): Report {
        $unapprovedExists = IndicatorValue::query()
            ->where('year', $year)
            ->when($quarter !== null, fn ($q) => $q->where('quarter', $quarter))
            ->where('status', '!=', 'approved')
            ->exists();

        $scorecard = $this->dashboard->getScorecard([
            'year' => $year,
            'quarter' => $quarter,
        ]);

        $rfCategory = IndicatorCategory::where('slug', 'results-framework')
            ->with(['indicators.targets'])
            ->first();

        $isrRows = [];
        if ($rfCategory) {
            foreach ($rfCategory->indicators as $indicator) {
                $actual = IndicatorValue::query()
                    ->where('indicator_id', $indicator->id)
                    ->where('year', $year)
                    ->when($quarter !== null, fn ($q) => $q->where('quarter', $quarter))
                    ->sum('value');

                $isrRows[] = [
                    'code' => $indicator->code,
                    'name' => $indicator->name,
                    'unit' => $indicator->unit,
                    'baseline' => $indicator->baseline_value,
                    'target' => $indicator->targetForYear($year),
                    'actual' => (float) $actual,
                    'status' => $indicator->statusLabel((float) $actual, $year),
                ];
            }
        }

        $title = match ($reportType) {
            'quarterly' => "SPIN Quarterly M&E Report — {$year} Q{$quarter}",
            'annual' => "SPIN Annual M&E Report — {$year}",
            default => "SPIN Category Report — {$year}",
        };

        return Report::create([
            'title' => $title,
            'report_type' => $reportType,
            'category_id' => $categoryId,
            'year' => $year,
            'quarter' => $quarter,
            'owner_type' => $actor->owner_type,
            'owner_id' => $actor->owner_id,
            'has_draft_watermark' => $unapprovedExists,
            'status' => 'draft',
            'narrative_sections' => [
                'executive_summary' => '',
                'challenges' => '',
                'next_priorities' => '',
            ],
            'snapshot_data' => [
                'scorecard' => $scorecard['scorecard'],
                'isr_rows' => $isrRows,
            ],
            'generated_by' => $actor->id,
        ]);
    }

    public function exportCsv(Report $report): string
    {
        $rows = $report->snapshot_data['isr_rows'] ?? [];
        $lines = ['Code,Indicator,Unit,Baseline,Target,Actual,Status'];

        foreach ($rows as $row) {
            $lines[] = implode(',', [
                $row['code'],
                '"'.str_replace('"', '""', $row['name']).'"',
                $row['unit'] ?? '',
                $row['baseline'] ?? '',
                $row['target'] ?? '',
                $row['actual'] ?? '',
                $row['status'] ?? '',
            ]);
        }

        return implode("\n", $lines)."\n";
    }
}
