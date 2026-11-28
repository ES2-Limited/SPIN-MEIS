<?php

namespace App\Livewire;

use App\Models\Indicator;
use App\Models\IndicatorValue;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IndicatorDetail extends Component
{
    public Indicator $indicator;

    public int $year = 2025;

    public int $quarter = 1;

    public string $value = '';

    public string $comment = '';

    public function mount(string $code): void
    {
        $this->indicator = Indicator::where('code', $code)
            ->with(['category', 'subgroup', 'targets'])
            ->firstOrFail();
    }

    public function saveEntry(): void
    {
        $this->validate([
            'year' => ['required', 'integer', 'min:2025', 'max:2035'],
            'quarter' => ['required', 'integer', 'min:1', 'max:4'],
            'value' => ['required', 'numeric'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        IndicatorValue::create([
            'indicator_id' => $this->indicator->id,
            'owner_type' => $user?->owner_type ?? 'national',
            'owner_id' => $user?->owner_id,
            'year' => $this->year,
            'quarter' => $this->quarter,
            'value' => (float) $this->value,
            'comment' => $this->comment ?: null,
            'channel' => 'manual',
            'source' => 'manual_entry',
            'status' => 'draft',
        ]);

        $this->reset(['value', 'comment']);
        session()->flash('status', 'Quarterly value saved as Draft.');
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
