<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">Overview</div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">SPIN MEIS 2.0 Dashboard</h1>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="year" class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm bg-white">
                @foreach([2025, 2026, 2027, 2028, 2029] as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
            <select wire:model.live="quarter" class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm bg-white">
                <option value="">All Quarters</option>
                <option value="1">Q1</option>
                <option value="2">Q2</option>
                <option value="3">Q3</option>
                <option value="4">Q4</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">On Track</div>
            <div class="text-2xl font-bold text-teal-700">{{ $headline['on_track'] }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">Needs Attention</div>
            <div class="text-2xl font-bold text-amber-600">{{ $headline['needs_attention'] }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">Off Track</div>
            <div class="text-2xl font-bold text-rose-600">{{ $headline['off_track'] }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">No Target</div>
            <div class="text-2xl font-bold text-slate-600">{{ $headline['no_target'] }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs text-slate-500">Pending Approvals</div>
            <div class="text-2xl font-bold text-amber-700">{{ $overdue_approvals }}</div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-x-auto">
        <div class="px-5 py-4 border-b border-slate-200 font-semibold text-slate-900">Category Scorecard</div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">On Track</th>
                    <th class="px-4 py-3">Needs Attention</th>
                    <th class="px-4 py-3">Off Track</th>
                    <th class="px-4 py-3">No Target</th>
                    <th class="px-4 py-3">No Data</th>
                    <th class="px-4 py-3">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($scorecard as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ url('/categories/' . $row['slug']) }}" class="text-teal-700 hover:underline">{{ $row['name'] }}</a>
                        </td>
                        <td class="px-4 py-3 text-teal-700 font-semibold">{{ $row['on_track'] }}</td>
                        <td class="px-4 py-3 text-amber-600 font-semibold">{{ $row['needs_attention'] }}</td>
                        <td class="px-4 py-3 text-rose-600 font-semibold">{{ $row['off_track'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $row['no_target'] }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $row['no_data'] }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $row['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
