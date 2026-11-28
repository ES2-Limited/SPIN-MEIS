<div class="space-y-6">
    <div class="rounded-2xl p-6 text-white bg-gradient-to-r from-amber-950 via-amber-900 to-slate-900 border border-amber-800 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-black/25 border border-amber-300/40 text-amber-200">◎ M&amp;E Framework</span>
                <h1 class="text-2xl font-bold tracking-tight mt-2">Monitoring &amp; Evaluation Dashboard</h1>
                <p class="text-xs text-amber-100/90 mt-1 max-w-2xl">Track Results Framework, Communication, Behaviour Change, GRM, and Gender indicators across all participating states and river basins.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ url('/categories/results-framework') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white/15 hover:bg-white/25 border border-white/30 text-white transition">Results Framework</a>
                    <a href="{{ url('/categories/communication-framework') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white/15 hover:bg-white/25 border border-white/30 text-white transition">Communications</a>
                    <a href="{{ url('/categories/grievance-mechanism-kpis') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white/15 hover:bg-white/25 border border-white/30 text-white transition">GRM</a>
                    <a href="{{ url('/categories/gender-social-inclusion') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white/15 hover:bg-white/25 border border-white/30 text-white transition">GBV / Gender</a>
                    <a href="{{ route('uploads.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white/15 hover:bg-white/25 border border-white/30 text-white transition">Bulk Upload</a>
                    <a href="{{ route('reports') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white/15 hover:bg-white/25 border border-white/30 text-white transition">Reports</a>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <label for="dashboard-year" class="sr-only">Reporting Year</label>
                <select id="dashboard-year" wire:model.live="year" class="border border-white/20 rounded-lg px-3 py-1.5 text-sm bg-white/10 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @foreach([2025, 2026, 2027, 2028, 2029] as $y)
                        <option value="{{ $y }}" class="text-slate-900">{{ $y }}</option>
                    @endforeach
                </select>
                <label for="dashboard-quarter" class="sr-only">Reporting Quarter</label>
                <select id="dashboard-quarter" wire:model.live="quarter" class="border border-white/20 rounded-lg px-3 py-1.5 text-sm bg-white/10 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="" class="text-slate-900">All Quarters</option>
                    <option value="1" class="text-slate-900">Q1</option>
                    <option value="2" class="text-slate-900">Q2</option>
                    <option value="3" class="text-slate-900">Q3</option>
                    <option value="4" class="text-slate-900">Q4</option>
                </select>
            </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Category Status SVG Bar Chart --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Indicator Status by Category</h2>
                    <p class="text-xs text-slate-500">On track (teal) vs Attention / Off track (amber)</p>
                </div>
                <div class="flex items-center gap-3 text-[11px] text-slate-600">
                    <span class="inline-flex items-center gap-1"><i class="w-2.5 h-2.5 rounded-sm bg-teal-600 inline-block"></i>On track</span>
                    <span class="inline-flex items-center gap-1"><i class="w-2.5 h-2.5 rounded-sm bg-amber-500 inline-block"></i>Attention/Off</span>
                </div>
            </div>
            @php
                $maxCat = max(1, collect($scorecard)->max('total') ?? 1);
            @endphp
            <div class="space-y-3">
                @foreach($scorecard as $row)
                    @php
                        $onW = round(($row['on_track'] / $maxCat) * 100);
                        $warnW = round((($row['needs_attention'] + $row['off_track']) / $maxCat) * 100);
                    @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-medium text-slate-700 truncate">{{ $row['name'] }}</span>
                            <span class="text-slate-500 tabular-nums">{{ $row['on_track'] }} / {{ $row['total'] }}</span>
                        </div>
                        <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden flex">
                            @if($onW > 0)
                                <div class="bg-teal-600 h-full transition-all" style="width: {{ $onW }}%"></div>
                            @endif
                            @if($warnW > 0)
                                <div class="bg-amber-500 h-full transition-all" style="width: {{ $warnW }}%"></div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Overall Portfolio Health Donut / Summary --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Portfolio Health ({{ $year }})</h2>
                <p class="text-xs text-slate-500">Distribution across all {{ $headline['total_indicators'] }} indicators</p>
            </div>
            @php
                $tot = max(1, $headline['total_indicators']);
                $bars = [
                    ['label' => 'On Track', 'count' => $headline['on_track'], 'color' => 'bg-teal-600'],
                    ['label' => 'Needs Attention', 'count' => $headline['needs_attention'], 'color' => 'bg-amber-500'],
                    ['label' => 'Off Track', 'count' => $headline['off_track'], 'color' => 'bg-rose-600'],
                    ['label' => 'No Target', 'count' => $headline['no_target'], 'color' => 'bg-slate-400'],
                    ['label' => 'No Data', 'count' => $headline['no_data'], 'color' => 'bg-slate-200'],
                ];
            @endphp
            <div class="space-y-2.5 my-4">
                @foreach($bars as $b)
                    <div class="grid grid-cols-12 items-center gap-2 text-xs">
                        <span class="col-span-4 text-slate-600 font-medium">{{ $b['label'] }}</span>
                        <div class="col-span-6 h-2.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="{{ $b['color'] }} h-full rounded-full" style="width: {{ round(($b['count'] / $tot) * 100) }}%"></div>
                        </div>
                        <span class="col-span-2 text-right font-semibold text-slate-800 tabular-nums">{{ $b['count'] }}</span>
                    </div>
                @endforeach
            </div>
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
