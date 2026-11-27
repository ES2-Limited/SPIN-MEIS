<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class SectionPage extends Component
{
    public string $title = 'Dashboard';

    public string $group = 'Overview';

    public string $description = '';

    public function mount(string $title = 'Dashboard', string $group = 'Overview', string $description = ''): void
    {
        $this->title = $title;
        $this->group = $group;
        $this->description = $description;
    }

    public function render(): View
    {
        return view('livewire.section-page')
            ->layout('components.layouts.app', ['title' => $this->title]);
    }
}
