<?php

namespace App\Livewire;

use App\Models\Scheme;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RiverBasinRegister extends Component
{
    public string $viewMode = 'scheme'; // state, model_1, model_2, scheme

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function render(): View
    {
        $query = Scheme::query()->with(['state', 'rbda', 'riverBasin']);

        if ($this->viewMode === 'model_1') {
            $query->where('delivery_model', 'model_1');
        } elseif ($this->viewMode === 'model_2') {
            $query->where('delivery_model', 'model_2');
        } elseif ($this->viewMode === 'state') {
            $query->orderBy('state_id');
        } else {
            $query->orderBy('name');
        }

        return view('livewire.river-basin-register', [
            'schemes' => $query->get(),
        ])->layout('components.layouts.app', ['title' => 'River Basin']);
    }
}
