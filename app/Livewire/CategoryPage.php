<?php

namespace App\Livewire;

use App\Models\Indicator;
use App\Models\IndicatorCategory;
use App\Models\IndicatorValue;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class CategoryPage extends Component
{
    public IndicatorCategory $category;

    #[Url(as: 'subgroup')]
    public ?int $activeSubgroupId = null;

    #[Url]
    public string $tab = 'indicators';

    public ?int $entryIndicatorId = null;

    public string $entryIndicatorCode = '';

    public string $entryIndicatorName = '';

    public int $entryYear = 2025;

    public string $q1 = '';

    public string $q2 = '';

    public string $q3 = '';

    public string $q4 = '';

    public string $entryComment = '';

    public function mount(string $slug): void
    {
        $this->category = IndicatorCategory::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function selectSubgroup(?int $subgroupId): void
    {
        $this->tab = 'indicators';
        $this->activeSubgroupId = $subgroupId;
    }

    public function selectTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function openEntryModal(int $indicatorId): void
    {
        $ind = Indicator::findOrFail($indicatorId);
        $this->entryIndicatorId = $ind->id;
        $this->entryIndicatorCode = $ind->code;
        $this->entryIndicatorName = $ind->name;
        $this->loadQuarterValues();
    }

    public function updatedEntryYear(): void
    {
        $this->loadQuarterValues();
    }

    protected function loadQuarterValues(): void
    {
        if (! $this->entryIndicatorId) {
            return;
        }
        $existing = IndicatorValue::where('indicator_id', $this->entryIndicatorId)
            ->where('year', $this->entryYear)
            ->get()
            ->keyBy('quarter');

        $this->q1 = (string) ($existing->get(1)?->value ?? '');
        $this->q2 = (string) ($existing->get(2)?->value ?? '');
        $this->q3 = (string) ($existing->get(3)?->value ?? '');
        $this->q4 = (string) ($existing->get(4)?->value ?? '');
        $this->entryComment = (string) ($existing->first()?->comment ?? '');
    }

    public function closeEntryModal(): void
    {
        $this->entryIndicatorId = null;
    }

    public function saveEntry(): void
    {
        $this->validate([
            'entryIndicatorId' => ['required', 'exists:indicators,id'],
            'entryYear' => ['required', 'integer', 'min:2025', 'max:2035'],
            'q1' => ['nullable', 'numeric'],
            'q2' => ['nullable', 'numeric'],
            'q3' => ['nullable', 'numeric'],
            'q4' => ['nullable', 'numeric'],
            'entryComment' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        foreach ([1 => $this->q1, 2 => $this->q2, 3 => $this->q3, 4 => $this->q4] as $q => $val) {
            if ($val === '' || $val === null) {
                continue;
            }
            IndicatorValue::updateOrCreate(
                [
                    'indicator_id' => $this->entryIndicatorId,
                    'owner_type' => $user?->owner_type ?? 'national',
                    'owner_id' => $user?->owner_id,
                    'year' => $this->entryYear,
                    'quarter' => $q,
                ],
                [
                    'value' => (float) $val,
                    'comment' => $this->entryComment ?: null,
                    'channel' => 'manual',
                    'source' => 'manual_entry',
                    'status' => 'draft',
                ]
            );
        }

        $this->entryIndicatorId = null;
        session()->flash('status', "Saved {$this->entryYear} quarterly data for {$this->entryIndicatorCode}.");
    }

    public function render(): View
    {
        $subgroups = $this->category->subgroups()->where('is_active', true)->get();

        $indicatorsQuery = $this->category->indicators()
            ->where('is_active', true)
            ->with(['targets', 'subgroup']);

        if ($this->activeSubgroupId !== null) {
            $indicatorsQuery->where('subgroup_id', $this->activeSubgroupId);
        }

        $indicators = $indicatorsQuery->get();

        $noTargetCount = $indicators->filter(fn ($ind) => $ind->targetForYear() === null)->count();

        return view('livewire.category-page', [
            'subgroups' => $subgroups,
            'indicators' => $indicators,
            'noTargetCount' => $noTargetCount,
        ])->layout('components.layouts.app', ['title' => $this->category->name]);
    }
}
