<header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/80 px-4 backdrop-blur-md dark:border-slate-700 dark:bg-slate-900/80 sm:px-6">
    {{-- Mobile menu --}}
    <button
        @click="$store.app.openMobileSidebar()"
        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800"
    >
        <i data-lucide="menu" class="h-5 w-5"></i>
    </button>

    {{-- Search --}}
    <div class="relative hidden flex-1 max-w-md sm:block">
        <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
        <input
            type="search"
            placeholder="Cari peserta, lomba, pertandingan..."
            class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm placeholder:text-slate-400 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-800"
        >
    </div>

    <div class="ml-auto flex items-center gap-2 sm:gap-3">
        {{-- Event selector --}}
        @if ($events && $events->count())
            <form action="{{ route('events.switch') }}" method="POST" class="hidden sm:block">
                @csrf
                <select
                    name="event_id"
                    onchange="this.form.submit()"
                    class="rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-8 text-sm font-medium text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                >
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" @selected($activeEvent && $activeEvent->id === $event->id)>
                            {{ $event->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif

        {{-- Notifications --}}
        <button class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
            <i data-lucide="bell" class="h-5 w-5"></i>
            <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-primary"></span>
        </button>

        {{-- Dark mode --}}
        <button
            @click="$store.app.toggleDarkMode()"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
        >
            <i x-show="!$store.app.darkMode" data-lucide="moon" class="h-5 w-5"></i>
            <i x-show="$store.app.darkMode" x-cloak data-lucide="sun" class="h-5 w-5"></i>
        </button>

        {{-- Profile dropdown --}}
        <div x-data="{ open: false }" class="relative">
            <button
                @click="open = !open"
                class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800"
            >
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="hidden text-sm font-medium text-secondary dark:text-slate-200 md:block">
                    {{ auth()->user()->name }}
                </span>
                <i data-lucide="chevron-down" class="hidden h-4 w-4 text-slate-400 md:block"></i>
            </button>

            <div
                x-show="open"
                @click.outside="open = false"
                x-transition
                x-cloak
                class="absolute right-0 mt-2 w-48 rounded-xl border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-800"
            >
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700">
                    <i data-lucide="user" class="h-4 w-4"></i>
                    Profil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
