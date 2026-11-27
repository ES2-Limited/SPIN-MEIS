<div class="space-y-6">
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">{{ $indicator->category?->name }}</div>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ $indicator->code }} — {{ $indicator->name }}</h1>
        <p class="text-sm text-slate-600 mt-1">
            Unit: {{ $indicator->unit ?? '—' }} · Mode: {{ $indicator->data_mode }} · Polarity: {{ ucfirst($indicator->polarity) }} is better
        </p>
    </div>

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
