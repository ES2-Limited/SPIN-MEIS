<?php

namespace App\Livewire;

use App\Models\UploadImport;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FileUploadManager extends Component
{
    public function render(): View
    {
        return view('livewire.file-upload-manager', [
            'imports' => UploadImport::with('uploader')->latest('id')->limit(50)->get(),
        ])->layout('components.layouts.app', ['title' => 'File Upload']);
    }
}
