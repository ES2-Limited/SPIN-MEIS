<div class="space-y-6">
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">Indicator Category</div>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-sm text-slate-600 mt-1">{{ $category->description }}</p>
        @endif
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">Indicators</div>
            <div class="text-2xl font-bold text-teal-800">{{ $indicators->count() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">Sub-groups</div>
            <div class="text-2xl font-bold text-teal-800">{{ $subgroups->count() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">No Target</div>
            <div class="text-2xl font-bold text-amber-600">{{ $noTargetCount }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">Prefix</div>
            <div class="text-2xl font-bold text-slate-700">{{ $category->code_prefix ?? '—' }}</div>
        </div>
    </div>

    {{-- Sub-group tabs --}}
    <div role="tablist" aria-label="Indicator sub-groups" class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
        <button role="tab" aria-selected="{{ $activeSubgroupId === null ? 'true' : 'false' }}" wire:click="selectSubgroup(null)" class="px-3 py-1.5 rounded-lg text-xs font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 {{ $activeSubgroupId === null ? 'bg-teal-700 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}">
            All
        </button>
        @foreach($subgroups as $sg)
            <button role="tab" aria-selected="{{ $activeSubgroupId === $sg->id ? 'true' : 'false' }}" wire:click="selectSubgroup({{ $sg->id }})" class="px-3 py-1.5 rounded-lg text-xs font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 {{ $activeSubgroupId === $sg->id ? 'bg-teal-700 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                {{ $sg->name }}
            </button>
        @endforeach
    </div>

    {{-- Indicator table --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Indicator</th>
                    <th class="px-4 py-3">Unit</th>
                    <th class="px-4 py-3">Baseline</th>
                    <th class="px-4 py-3">Target</th>
                    <th class="px-4 py-3">Latest Actual</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Mode</th>
                    <th class="px-4 py-3">Source</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($indicators as $ind)
                    @php
                        $target = $ind->targetForYear();
                        $status = $ind->statusLabel(null);
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-teal-800">{{ $ind->code }}</td>
                        <td class="px-4 py-3 text-slate-900">
                            {{ $ind->name }}
                            @if($ind->shared_group_key)
                                <span class="ml-1 px-1.5 py-0.5 text-[10px] rounded bg-teal-100 text-teal-800 font-semibold">Shared</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $ind->unit ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $ind->baseline_value !== null ? rtrim(rtrim((string) $ind->baseline_value, '0'), '.') : '—' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $target !== null ? $target : '—' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-400">—</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $status === 'No target' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-800' }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $ind->data_mode }}</td>
                        <td class="px-4 py-3 text-xs">{{ $ind->source_system ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-6 text-center text-slate-500">No indicators in this view yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
