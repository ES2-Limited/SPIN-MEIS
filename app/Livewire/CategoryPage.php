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

    public int $entryQuarter = 1;

    public string $entryValue = '';

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
        $this->entryValue = '';
        $this->entryComment = '';
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
            'entryQuarter' => ['required', 'integer', 'min:1', 'max:4'],
            'entryValue' => ['required', 'numeric'],
            'entryComment' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        IndicatorValue::create([
            'indicator_id' => $this->entryIndicatorId,
            'owner_type' => $user?->owner_type ?? 'national',
            'owner_id' => $user?->owner_id,
            'year' => $this->entryYear,
            'quarter' => $this->entryQuarter,
            'value' => (float) $this->entryValue,
            'comment' => $this->entryComment ?: null,
            'channel' => 'manual',
            'source' => 'manual_entry',
            'status' => 'draft',
        ]);

        $this->entryIndicatorId = null;
        session()->flash('status', "Saved Draft for {$this->entryIndicatorCode}.");
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
