<div class="space-y-6">
    <div class="rounded-2xl p-6 text-white bg-gradient-to-r from-teal-950 via-teal-800 to-slate-900 border border-teal-800 shadow-md">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-black/25 border border-teal-300/40 text-teal-200">◎ Indicator Category</span>
        <h1 class="text-2xl font-bold tracking-tight mt-2">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-xs text-teal-100/90 mt-1 max-w-2xl">{{ $category->description }}</p>
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
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($indicators as $ind)
                    @php
                        $target = $ind->targetForYear();
                        $status = $ind->statusLabel(null);
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-teal-800">
                            <a href="{{ route('indicators.show', $ind->code) }}" class="hover:underline">{{ $ind->code }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-900">
                            <a href="{{ route('indicators.show', $ind->code) }}" class="hover:text-teal-700 font-medium">{{ $ind->name }}</a>
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
                        <td class="px-4 py-3 text-xs">
                            <button type="button" wire:click="openEntryModal({{ $ind->id }})" class="inline-flex items-center px-2.5 py-1 rounded-md bg-teal-700 hover:bg-teal-800 text-white font-semibold">Enter</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-6 text-center text-slate-500">No indicators in this view yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($entryIndicatorId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="entry-modal-title">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-mono font-semibold text-teal-700">{{ $entryIndicatorCode }}</div>
                        <h2 id="entry-modal-title" class="text-base font-bold text-slate-900">Enter Quarterly Value</h2>
                    </div>
                    <button type="button" wire:click="closeEntryModal" class="text-slate-400 hover:text-slate-600 text-xl leading-none" aria-label="Close">&times;</button>
                </div>
                <form wire:submit="saveEntry" class="p-5 space-y-4">
                    <p class="text-xs text-slate-600">{{ $entryIndicatorName }}</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="modal-year" class="block text-xs font-semibold text-slate-600 mb-1">Year</label>
                            <select id="modal-year" wire:model="entryYear" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                                @foreach([2025, 2026, 2027, 2028, 2029] as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="modal-quarter" class="block text-xs font-semibold text-slate-600 mb-1">Quarter</label>
                            <select id="modal-quarter" wire:model="entryQuarter" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                                <option value="1">Q1</option>
                                <option value="2">Q2</option>
                                <option value="3">Q3</option>
                                <option value="4">Q4</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="modal-value" class="block text-xs font-semibold text-slate-600 mb-1">Value</label>
                        <input id="modal-value" wire:model="entryValue" type="number" step="any" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="modal-comment" class="block text-xs font-semibold text-slate-600 mb-1">Notes / Evidence</label>
                        <input id="modal-comment" wire:model="entryComment" type="text" placeholder="Optional note" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="closeEntryModal" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold">Save Draft</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
