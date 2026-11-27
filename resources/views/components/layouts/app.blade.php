<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f766e">
    <title>{{ $title ?? 'SPIN MEIS 2.0' }} — SPIN MEIS 2.0</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/icons/icon-192.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen flex flex-col md:flex-row">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:px-4 focus:py-2 focus:rounded-lg focus:bg-amber-400 focus:text-slate-950 focus:font-semibold focus:shadow-lg">
        Skip to main content
    </a>

    {{-- Desktop Sidebar --}}
    <aside aria-label="Sidebar" class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 bg-teal-900 text-teal-50 border-r border-teal-800">
        <div class="flex items-center gap-3 px-4 py-4 border-b border-teal-800">
            <img src="/icons/icon-192.svg" alt="SPIN MEIS 2.0 logo" class="w-8 h-8 rounded-lg">
            <div>
                <div class="font-bold text-sm tracking-wide text-white">SPIN MEIS 2.0</div>
                <div class="text-[11px] text-amber-300 font-medium">FMWR&amp;S · Nigeria</div>
            </div>
        </div>

        <nav aria-label="Primary" class="flex-1 overflow-y-auto px-3 py-4 space-y-5 text-sm">
            <div>
                <div class="px-2 mb-1 text-[11px] font-semibold uppercase tracking-wider text-teal-300">Overview</div>
                <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif class="flex items-center gap-2 px-2.5 py-2 rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 {{ request()->routeIs('dashboard') ? 'bg-teal-800 text-amber-300 font-semibold' : 'hover:bg-teal-800/60 text-teal-100' }}">
                    <span>Dashboard</span>
                </a>
            </div>

            <div>
                <div class="px-2 mb-1 text-[11px] font-semibold uppercase tracking-wider text-teal-300">Indicator Categories</div>
                <div class="space-y-0.5">
                    @php
                        $navCategories = \Illuminate\Support\Facades\Schema::hasTable('indicator_categories')
                            ? \App\Models\IndicatorCategory::where('is_active', true)->with(['subgroups' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])->orderBy('sort_order')->get()
                            : collect();
                    @endphp
                    @forelse($navCategories as $navCat)
                        @php $catOpen = request()->is('categories/' . $navCat->slug); @endphp
                        <div x-data="{ open: {{ $catOpen ? 'true' : 'false' }} }">
                            <button type="button" @click="open = !open" aria-expanded="{{ $catOpen ? 'true' : 'false' }}" :aria-expanded="open.toString()" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-md text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 {{ $catOpen ? 'bg-teal-800 text-amber-300 font-semibold' : 'hover:bg-teal-800/60 text-teal-100' }}">
                                <span class="truncate">{{ $navCat->name }}</span>
                                <svg class="w-3.5 h-3.5 shrink-0 transition-transform" :class="open ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" x-cloak class="ml-4 mt-0.5 space-y-0.5 border-l border-teal-700/70 pl-2.5 text-xs">
                                <a href="{{ url('/categories/' . $navCat->slug) }}" class="block py-1 text-teal-200 hover:text-amber-300">All Indicators</a>
                                @foreach($navCat->subgroups as $sg)
                                    <a href="{{ url('/categories/' . $navCat->slug . '?subgroup=' . $sg->id) }}" class="block py-1 text-teal-200 hover:text-amber-300 truncate">{{ $sg->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <a href="{{ url('/categories/results-framework') }}" class="block px-2.5 py-1.5 rounded-md hover:bg-teal-800/60 text-teal-100">Results Framework (PAD)</a>
                    @endforelse
                    <a href="{{ route('impact-indicators') }}" class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('impact-indicators') ? 'bg-teal-800 text-amber-300 font-semibold' : 'hover:bg-teal-800/60 text-teal-100' }}">Impact Indicators</a>
                </div>
            </div>

            <div>
                <div class="px-2 mb-1 text-[11px] font-semibold uppercase tracking-wider text-teal-300">Project Data</div>
                <div class="space-y-0.5">
                    <a href="{{ route('project-data.river-basin') }}" class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('project-data.river-basin') ? 'bg-teal-800 text-amber-300 font-semibold' : 'hover:bg-teal-800/60 text-teal-100' }}">River Basin</a>
                    <a href="{{ route('project-data.procurement') }}" class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('project-data.procurement') ? 'bg-teal-800 text-amber-300 font-semibold' : 'hover:bg-teal-800/60 text-teal-100' }}">Procurement</a>
                    <a href="{{ route('project-data.awpb') }}" class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('project-data.awpb') ? 'bg-teal-800 text-amber-300 font-semibold' : 'hover:bg-teal-800/60 text-teal-100' }}">Annual Work Plan &amp; Budget</a>
                </div>
            </div>

            <div>
                <div class="px-2 mb-1 text-[11px] font-semibold uppercase tracking-wider text-teal-300">Reporting</div>
                <a href="{{ route('reports') }}" class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('reports') ? 'bg-teal-800 text-amber-300 font-semibold' : 'hover:bg-teal-800/60 text-teal-100' }}">Reports</a>
            </div>

            <div>
                <div class="px-2 mb-1 text-[11px] font-semibold uppercase tracking-wider text-teal-300">Administration</div>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.users') }}" class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('admin.users') ? 'bg-teal-800 text-amber-300 font-semibold' : 'hover:bg-teal-800/60 text-teal-100' }}">Users &amp; Roles</a>
                    <a href="{{ route('admin.integrations') }}" class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('admin.integrations') ? 'bg-teal-800 text-amber-300 font-semibold' : 'hover:bg-teal-800/60 text-teal-100' }}">Integrations</a>
                    <a href="{{ route('admin.data-backup') }}" class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('admin.data-backup') ? 'bg-teal-800 text-amber-300 font-semibold' : 'hover:bg-teal-800/60 text-teal-100' }}">Data &amp; Backup</a>
                </div>
            </div>
        </nav>
    </aside>

    {{-- Mobile Top Bar --}}
    <header class="md:hidden bg-teal-900 text-white px-4 py-3 flex items-center justify-between border-b border-teal-800">
        <div class="flex items-center gap-2">
            <img src="/icons/icon-192.svg" alt="SPIN MEIS" class="w-7 h-7 rounded">
            <span class="font-bold text-sm">SPIN MEIS 2.0</span>
        </div>
        <span class="text-xs px-2 py-0.5 rounded bg-amber-500 text-slate-950 font-semibold">Phase 0</span>
    </header>

    {{-- Main Content --}}
    <main id="main-content" tabindex="-1" class="flex-1 md:pl-64 pb-20 md:pb-8 focus:outline-none">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            {{ $slot }}
        </div>
    </main>

    {{-- Mobile Bottom Navigation --}}
    <nav aria-label="Mobile navigation" class="md:hidden fixed bottom-0 inset-x-0 bg-teal-900 text-teal-100 border-t border-teal-800 grid grid-cols-4 text-[11px] z-50">
        <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif class="flex flex-col items-center justify-center min-h-[48px] py-2.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 {{ request()->routeIs('dashboard') ? 'text-amber-300 font-semibold' : '' }}">
            <span>Dashboard</span>
        </a>
        <a href="{{ url('/categories/results-framework') }}" @if(request()->is('categories/*')) aria-current="page" @endif class="flex flex-col items-center justify-center min-h-[48px] py-2.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 {{ request()->is('categories/*') ? 'text-amber-300 font-semibold' : '' }}">
            <span>Indicators</span>
        </a>
        <a href="{{ route('project-data.river-basin') }}" @if(request()->is('project-data/*')) aria-current="page" @endif class="flex flex-col items-center justify-center min-h-[48px] py-2.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 {{ request()->is('project-data/*') ? 'text-amber-300 font-semibold' : '' }}">
            <span>Project</span>
        </a>
        <a href="{{ route('reports') }}" @if(request()->routeIs('reports')) aria-current="page" @endif class="flex flex-col items-center justify-center min-h-[48px] py-2.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 {{ request()->routeIs('reports') ? 'text-amber-300 font-semibold' : '' }}">
            <span>Reports</span>
        </a>
    </nav>

    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>
</html>
