<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AuditLogViewer extends Component
{
    public function render(): View
    {
        return view('livewire.admin.audit-log-viewer', [
            'logs' => AuditLog::with('user')->latest('id')->limit(100)->get(),
        ])->layout('components.layouts.app', ['title' => 'Audit Log']);
    }
}
