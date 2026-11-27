<?php

namespace App\Livewire;

use App\Models\IndicatorCategory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CategoryPage extends Component
{
    public IndicatorCategory $category;

    public ?int $activeSubgroupId = null;

    public function mount(string $slug): void
    {
        $this->category = IndicatorCategory::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function selectSubgroup(?int $subgroupId): void
    {
        $this->activeSubgroupId = $subgroupId;
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
