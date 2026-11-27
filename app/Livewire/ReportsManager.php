<?php

namespace App\Livewire;

use App\Models\Report;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ReportsManager extends Component
{
    public function render(): View
    {
        return view('livewire.reports-manager', [
            'reports' => Report::with('generator')->latest('id')->limit(50)->get(),
        ])->layout('components.layouts.app', ['title' => 'Reports']);
    }
}
