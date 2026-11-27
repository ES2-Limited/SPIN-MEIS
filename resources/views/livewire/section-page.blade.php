<div>
    <div class="mb-6">
        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">{{ $group }}</div>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ $title }}</h1>
        @if($description)
            <p class="text-sm text-slate-600 mt-1">{{ $description }}</p>
        @endif
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 mb-3">
            Phase 0 Skeleton
        </div>
        <p class="text-sm text-slate-600">
            This module shell is ready. Data tables, workflows, and role-scoped views will be populated in upcoming phases.
        </p>
    </div>
</div>
