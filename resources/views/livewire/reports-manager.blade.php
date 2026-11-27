<div class="space-y-6">
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">Reporting</div>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">M&amp;E Reports</h1>
        <p class="text-sm text-slate-600 mt-1">Quarterly, annual, and category reports from approved data (with draft watermark when unapproved values exist).</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th scope="col" class="px-4 py-3">Title</th>
                    <th scope="col" class="px-4 py-3">Type</th>
                    <th scope="col" class="px-4 py-3">Period</th>
                    <th scope="col" class="px-4 py-3">Watermark</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($reports as $report)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $report->title }}</td>
                        <td class="px-4 py-3 uppercase text-xs">{{ $report->report_type }}</td>
                        <td class="px-4 py-3 text-xs">{{ $report->year }}{{ $report->quarter ? ' Q'.$report->quarter : '' }}</td>
                        <td class="px-4 py-3">
                            @if($report->has_draft_watermark)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">DRAFT WATERMARK</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">Approved Clean</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs uppercase">{{ $report->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No reports generated yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
