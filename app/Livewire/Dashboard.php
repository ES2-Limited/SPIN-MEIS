<?php

namespace App\Livewire;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public int $year = 2025;

    public ?int $quarter = null;

    public function render(DashboardService $dashboard): View
    {
        $data = $dashboard->getScorecard([
            'year' => $this->year,
            'quarter' => $this->quarter,
        ]);

        return view('livewire.dashboard', $data)
            ->layout('components.layouts.app', ['title' => 'Dashboard']);
    }
}
