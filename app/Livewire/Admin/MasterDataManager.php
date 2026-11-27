<?php

namespace App\Livewire\Admin;

use App\Models\Rbda;
use App\Models\RiverBasin;
use App\Models\Scheme;
use App\Models\State;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MasterDataManager extends Component
{
    public function render(): View
    {
        return view('livewire.admin.master-data-manager', [
            'states' => State::orderBy('name')->get(),
            'rbdas' => Rbda::orderBy('name')->get(),
            'basins' => RiverBasin::with('rbda')->orderBy('name')->get(),
            'schemes' => Scheme::withoutGlobalScopes()->with(['state', 'rbda'])->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'Master Data']);
    }
}
