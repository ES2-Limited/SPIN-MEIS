<div class="space-y-6">
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">Data Capture</div>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">Bulk File Upload &amp; Import History</h1>
        <p class="text-sm text-slate-600 mt-1">Download indicator templates, validate spreadsheets with row-level preview, and commit as Draft.</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">File</th>
                    <th class="px-4 py-3">Uploaded By</th>
                    <th class="px-4 py-3">Rows (Valid / Error)</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Committed At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($imports as $imp)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $imp->filename }}</td>
                        <td class="px-4 py-3 text-xs">{{ $imp->uploader?->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $imp->total_rows }} ({{ $imp->valid_rows }} / {{ $imp->error_rows }})</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $imp->status === 'committed' ? 'bg-teal-100 text-teal-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst($imp->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $imp->committed_at?->toDateTimeString() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No imports yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
