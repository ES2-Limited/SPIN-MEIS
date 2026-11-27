<?php

namespace App\Services;

use App\Models\IndicatorCategory;
use App\Models\IndicatorValue;

class DashboardService
{
    /**
     * Compute category scorecard and headline metrics for the current user's owner scope.
     *
     * @param  array{year?: int|null, quarter?: int|null, scheme_id?: int|null}  $filters
     * @return array{
     *   headline: array{total_indicators: int, on_track: int, needs_attention: int, off_track: int, no_target: int, no_data: int},
     *   scorecard: array<int, array{category_id: int, slug: string, name: string, on_track: int, needs_attention: int, off_track: int, no_target: int, no_data: int, total: int}>,
     *   overdue_approvals: int
     * }
     */
    public function getScorecard(array $filters = []): array
    {
        $year = $filters['year'] ?? (int) date('Y');
        $quarter = $filters['quarter'] ?? null;
        $schemeId = $filters['scheme_id'] ?? null;

        $categories = IndicatorCategory::where('is_active', true)
            ->with(['indicators' => fn ($q) => $q->where('is_active', true)->with('targets')])
            ->orderBy('sort_order')
            ->get();

        $valuesQuery = IndicatorValue::query()->where('year', $year);
        if ($quarter !== null) {
            $valuesQuery->where('quarter', $quarter);
        }
        if ($schemeId !== null) {
            $valuesQuery->where('scheme_id', $schemeId);
        }

        $latestValues = $valuesQuery->get()
            ->groupBy('indicator_id')
            ->map(fn ($group) => $group->sum('value'));

        $headline = [
            'total_indicators' => 0,
            'on_track' => 0,
            'needs_attention' => 0,
            'off_track' => 0,
            'no_target' => 0,
            'no_data' => 0,
        ];

        $scorecard = [];

        foreach ($categories as $category) {
            $row = [
                'category_id' => $category->id,
                'slug' => $category->slug,
                'name' => $category->name,
                'on_track' => 0,
                'needs_attention' => 0,
                'off_track' => 0,
                'no_target' => 0,
                'no_data' => 0,
                'total' => $category->indicators->count(),
            ];

            foreach ($category->indicators as $indicator) {
                $actual = $latestValues->has($indicator->id)
                    ? (float) $latestValues->get($indicator->id)
                    : null;

                $status = $indicator->statusLabel($actual, $year);

                match ($status) {
                    'On track' => $row['on_track']++,
                    'Needs attention' => $row['needs_attention']++,
                    'Off track' => $row['off_track']++,
                    'No target' => $row['no_target']++,
                    default => $row['no_data']++,
                };
            }

            $headline['total_indicators'] += $row['total'];
            $headline['on_track'] += $row['on_track'];
            $headline['needs_attention'] += $row['needs_attention'];
            $headline['off_track'] += $row['off_track'];
            $headline['no_target'] += $row['no_target'];
            $headline['no_data'] += $row['no_data'];

            $scorecard[] = $row;
        }

        $overdueApprovals = IndicatorValue::query()
            ->whereIn('status', ['submitted', 'verified'])
            ->count();

        return [
            'headline' => $headline,
            'scorecard' => $scorecard,
            'overdue_approvals' => $overdueApprovals,
        ];
    }
}
