<div class="space-y-6">
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">Administration</div>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">Indicator Categories &amp; Sub-groups</h1>
        <p class="text-sm text-slate-600 mt-1">Create, order, and retire indicator categories. Active categories appear on the side menu automatically.</p>
    </div>

    <form wire:submit="createCategory" class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-3">
        <input wire:model="name" type="text" placeholder="Category name" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        <input wire:model="code_prefix" type="text" placeholder="Code prefix (e.g. ENV)" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        <input wire:model="sort_order" type="number" placeholder="Order" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white font-semibold rounded-lg px-4 py-2 text-sm">Add Category</button>
    </form>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Prefix</th>
                    <th class="px-4 py-3">Sub-groups</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($categories as $cat)
                    <tr>
                        <td class="px-4 py-3 text-xs">{{ $cat->sort_order }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $cat->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $cat->code_prefix ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $cat->subgroups->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $cat->is_active ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $cat->is_active ? 'Active' : 'Retired' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleCategory({{ $cat->id }})" class="text-xs font-semibold text-teal-700 hover:underline">
                                {{ $cat->is_active ? 'Retire' : 'Activate' }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
