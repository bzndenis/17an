@php
    $navItems = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
        ['route' => 'participants.index', 'label' => 'Peserta', 'icon' => 'users'],
        ['route' => 'competitions.index', 'label' => 'Lomba', 'icon' => 'trophy'],
        ['route' => 'matches.index', 'label' => 'Pertandingan', 'icon' => 'swords'],
        ['route' => 'rankings.global', 'label' => 'Peringkat', 'icon' => 'medal'],
        ['route' => 'schedules.index', 'label' => 'Jadwal', 'icon' => 'calendar'],
        ['route' => 'announcements.index', 'label' => 'Pengumuman', 'icon' => 'megaphone'],
        ['route' => 'settings.edit', 'label' => 'Pengaturan', 'icon' => 'settings'],
    ];
@endphp

<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col border-r border-slate-200 bg-white transition-all duration-300 dark:border-slate-700 dark:bg-slate-900"
    :class="[
        $store.app.mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        $store.app.sidebarCollapsed ? 'w-[72px]' : 'w-64'
    ]"
>
    {{-- Logo --}}
    <div class="flex h-16 items-center border-b border-slate-200 px-4 dark:border-slate-700"
         :class="$store.app.sidebarCollapsed ? 'justify-center' : 'justify-between'">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-white font-bold text-sm">
                17
            </div>
            <span x-show="!$store.app.sidebarCollapsed" x-cloak class="font-bold text-secondary dark:text-white">
                17an
            </span>
        </a>
        <button
            x-show="!$store.app.sidebarCollapsed"
            @click="$store.app.toggleSidebar()"
            class="hidden rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 lg:block dark:hover:bg-slate-800"
        >
            <i data-lucide="panel-left-close" class="h-5 w-5"></i>
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 space-y-1 overflow-y-auto p-3 scrollbar-thin">
        @foreach ($navItems as $item)
            @php
                $active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route']));
                if ($item['route'] === 'dashboard') {
                    $active = request()->routeIs('dashboard');
                }
            @endphp
            <a
                href="{{ route($item['route']) }}"
                class="sidebar-link {{ $active ? 'sidebar-link-active' : '' }}"
                :title="$store.app.sidebarCollapsed ? '{{ $item['label'] }}' : ''"
            >
                <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5 shrink-0"></i>
                <span x-show="!$store.app.sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Expand button when collapsed --}}
    <div class="border-t border-slate-200 p-3 dark:border-slate-700">
        <button
            x-show="$store.app.sidebarCollapsed"
            @click="$store.app.toggleSidebar()"
            class="flex w-full items-center justify-center rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
        >
            <i data-lucide="panel-left-open" class="h-5 w-5"></i>
        </button>
    </div>
</aside>
