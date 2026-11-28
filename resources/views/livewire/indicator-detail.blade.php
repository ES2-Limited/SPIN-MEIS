<div class="space-y-6">
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">{{ $indicator->category?->name }}</div>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ $indicator->code }} — {{ $indicator->name }}</h1>
        <p class="text-sm text-slate-600 mt-1">
            Unit: {{ $indicator->unit ?? '—' }} · Mode: {{ $indicator->data_mode }} · Polarity: {{ ucfirst($indicator->polarity) }} is better
        </p>
    </div>

    @if(session('status'))
        <div class="rounded-xl bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 text-sm font-medium">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="saveEntry" class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div>
            <label for="entry-year" class="block text-xs font-semibold text-slate-600 mb-1">Year</label>
            <select id="entry-year" wire:model="year" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                @foreach([2025, 2026, 2027, 2028, 2029] as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="entry-quarter" class="block text-xs font-semibold text-slate-600 mb-1">Quarter</label>
            <select id="entry-quarter" wire:model="quarter" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option value="1">Q1</option>
                <option value="2">Q2</option>
                <option value="3">Q3</option>
                <option value="4">Q4</option>
            </select>
        </div>
        <div>
            <label for="entry-value" class="block text-xs font-semibold text-slate-600 mb-1">Value ({{ $indicator->unit ?? 'Number' }})</label>
            <input id="entry-value" wire:model="value" type="number" step="any" placeholder="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label for="entry-comment" class="block text-xs font-semibold text-slate-600 mb-1">Notes / Evidence</label>
            <input id="entry-comment" wire:model="comment" type="text" placeholder="Optional note" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white font-semibold rounded-lg px-4 py-2 text-sm">Save Draft</button>
    </form>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Period</th>
                    <th class="px-4 py-3">Value</th>
                    <th class="px-4 py-3">Channel</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Lineage</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($values as $val)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $val->year }} Q{{ $val->quarter }}</td>
                        <td class="px-4 py-3">{{ $val->value ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs uppercase">{{ $val->channel }} ({{ $val->rule_version }})</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">{{ ucfirst($val->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">
                            Sub: {{ $val->submitter?->name ?? '—' }} · Ver: {{ $val->verifier?->name ?? '—' }} · App: {{ $val->approver?->name ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No quarterly values recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
