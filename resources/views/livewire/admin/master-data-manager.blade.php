<div class="space-y-6">
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">Administration</div>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">Master Data</h1>
        <p class="text-sm text-slate-600 mt-1">States, RBDAs, River Basins, and Schemes with computed Model 1 / Model 2 ownership.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">States</div>
            <div class="text-2xl font-bold text-teal-800">{{ $states->count() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">RBDAs</div>
            <div class="text-2xl font-bold text-teal-800">{{ $rbdas->count() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">River Basins</div>
            <div class="text-2xl font-bold text-teal-800">{{ $basins->count() }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">Schemes</div>
            <div class="text-2xl font-bold text-teal-800">{{ $schemes->count() }}</div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Scheme</th>
                    <th class="px-4 py-3">State</th>
                    <th class="px-4 py-3">RBDA</th>
                    <th class="px-4 py-3">Model</th>
                    <th class="px-4 py-3">Computed Owner</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($schemes as $scheme)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs">{{ $scheme->code }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $scheme->name }}</td>
                        <td class="px-4 py-3">{{ $scheme->state?->name }}</td>
                        <td class="px-4 py-3">{{ $scheme->rbda?->name }}</td>
                        <td class="px-4 py-3 uppercase text-xs font-semibold">{{ $scheme->delivery_model }}</td>
                        <td class="px-4 py-3 uppercase text-xs font-semibold text-teal-700">{{ $scheme->owner_type }} #{{ $scheme->owner_id }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No schemes configured yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
