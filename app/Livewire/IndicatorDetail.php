<?php

namespace App\Livewire;

use App\Models\Indicator;
use App\Models\IndicatorValue;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class IndicatorDetail extends Component
{
    public Indicator $indicator;

    public function mount(string $code): void
    {
        $this->indicator = Indicator::where('code', $code)
            ->with(['category', 'subgroup', 'targets'])
            ->firstOrFail();
    }

    public function render(): View
    {
        $values = IndicatorValue::where('indicator_id', $this->indicator->id)
            ->with(['submitter', 'verifier', 'approver'])
            ->orderByDesc('year')
            ->orderByDesc('quarter')
            ->get();

        return view('livewire.indicator-detail', [
            'values' => $values,
        ])->layout('components.layouts.app', ['title' => $this->indicator->code]);
    }
}
