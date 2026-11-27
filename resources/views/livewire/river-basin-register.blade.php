<div class="space-y-6">
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">Project Data</div>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">River Basin &amp; Scheme Register</h1>
        <p class="text-sm text-slate-600 mt-1">Four views: State, Model 1 (RBDA-managed), Model 2 (State-managed), and Scheme name.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach(['scheme' => 'By Scheme', 'state' => 'By State', 'model_1' => 'Model 1 (RBDA)', 'model_2' => 'Model 2 (State)'] as $key => $label)
            <button wire:click="setViewMode('{{ $key }}')" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $viewMode === $key ? 'bg-teal-700 text-white' : 'bg-white border border-slate-200 text-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
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
                    <th class="px-4 py-3">Owner</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($schemes as $scheme)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs">{{ $scheme->code }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $scheme->name }}</td>
                        <td class="px-4 py-3">{{ $scheme->state?->name ?? 'Missing' }}</td>
                        <td class="px-4 py-3">{{ $scheme->rbda?->name ?? 'Missing' }}</td>
                        <td class="px-4 py-3 uppercase text-xs font-semibold">{{ $scheme->delivery_model }}</td>
                        <td class="px-4 py-3 uppercase text-xs font-semibold text-teal-700">{{ $scheme->owner_type }} #{{ $scheme->owner_id }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No schemes in your scope.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
