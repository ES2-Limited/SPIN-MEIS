<div class="space-y-6">
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">Administration</div>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">Audit Log</h1>
        <p class="text-sm text-slate-600 mt-1">Immutable record of sign-ins, role changes, and data mutations.</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">When</th>
                    <th class="px-4 py-3">Who</th>
                    <th class="px-4 py-3">Event</th>
                    <th class="px-4 py-3">Record</th>
                    <th class="px-4 py-3">Scope</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $log->created_at?->toDateTimeString() }}</td>
                        <td class="px-4 py-3">{{ $log->user?->email ?? 'System' }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-teal-700">{{ $log->event }}</td>
                        <td class="px-4 py-3 text-xs">{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}</td>
                        <td class="px-4 py-3 text-xs uppercase">{{ $log->owner_type ?? '—' }} {{ $log->owner_id ? '#'.$log->owner_id : '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No audit entries yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
