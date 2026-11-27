<?php

namespace App\Livewire\Admin;

use App\Models\IndicatorCategory;
use App\Models\IndicatorSubgroup;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class CategoriesManager extends Component
{
    public string $name = '';

    public string $code_prefix = '';

    public int $sort_order = 10;

    public string $subgroup_name = '';

    public ?int $selected_category_id = null;

    public function createCategory(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code_prefix' => ['nullable', 'string', 'max:16'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        IndicatorCategory::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'code_prefix' => $this->code_prefix ?: null,
            'sort_order' => $this->sort_order,
            'is_active' => true,
        ]);

        $this->reset(['name', 'code_prefix']);
    }

    public function addSubgroup(): void
    {
        $this->validate([
            'selected_category_id' => ['required', 'exists:indicator_categories,id'],
            'subgroup_name' => ['required', 'string', 'max:255'],
        ]);

        IndicatorSubgroup::create([
            'category_id' => $this->selected_category_id,
            'name' => $this->subgroup_name,
            'slug' => Str::slug($this->subgroup_name),
            'sort_order' => IndicatorSubgroup::where('category_id', $this->selected_category_id)->count() + 1,
            'is_active' => true,
        ]);

        $this->reset(['subgroup_name']);
    }

    public function toggleCategory(int $id): void
    {
        $cat = IndicatorCategory::findOrFail($id);
        $cat->update(['is_active' => ! $cat->is_active]);
    }

    public function render(): View
    {
        return view('livewire.admin.categories-manager', [
            'categories' => IndicatorCategory::with('subgroups')->orderBy('sort_order')->get(),
        ])->layout('components.layouts.app', ['title' => 'Manage Categories']);
    }
}
