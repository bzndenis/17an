<x-app-layout title="Dashboard">
    <x-ui.page-header
        title="Dashboard"
        :description="$activeEvent ? 'Ringkasan event ' . $activeEvent->name : 'Kelola kompetisi 17an'"
    />

    @if ($noEvent ?? false)
        <x-ui.empty-state
            title="Belum Ada Event Aktif"
            description="Silakan pilih atau buat event terlebih dahulu untuk melihat dashboard."
            icon="calendar-x"
        />
    @else
        {{-- Stat Cards --}}
        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <x-ui.stat-card label="Total Peserta" :value="$stats['total_participants'] ?? 0" icon="users" color="primary" />
            <x-ui.stat-card label="Total Lomba" :value="$stats['total_competitions'] ?? 0" icon="trophy" color="amber" />
            <x-ui.stat-card label="Live" :value="$stats['live_matches'] ?? 0" icon="radio" color="red" />
            <x-ui.stat-card label="Selesai" :value="$stats['finished_matches'] ?? 0" icon="check-circle" color="green" />
            <x-ui.stat-card label="Tersingkir" :value="$stats['eliminated_participants'] ?? 0" icon="user-x" color="purple" />
            <x-ui.stat-card label="Penghargaan" :value="$stats['total_awards'] ?? 0" icon="award" color="blue" />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Competition Overview --}}
            <div class="lg:col-span-2">
                <x-ui.card>
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-secondary dark:text-white">Ringkasan Lomba</h2>
                        <x-ui.button variant="ghost" size="sm" :href="route('competitions.index')">
                            Lihat Semua
                            <i data-lucide="arrow-right" class="h-4 w-4"></i>
                        </x-ui.button>
                    </div>

                    @if ($competitionOverview->isEmpty())
                        <x-ui.empty-state
                            title="Belum Ada Lomba"
                            description="Mulai buat lomba pertama untuk event ini."
                            icon="trophy"
                            :actionHref="route('competitions.create')"
                            actionLabel="Buat Lomba"
                        />
                    @else
                        <div class="space-y-4">
                            @foreach ($competitionOverview as $competition)
                                <div class="rounded-lg border border-slate-100 p-4 dark:border-slate-700">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="min-w-0 flex-1">
                                            <a href="{{ route('competitions.show', $competition) }}" class="font-medium text-secondary hover:text-primary dark:text-white dark:hover:text-red-400">
                                                {{ $competition->name }}
                                            </a>
                                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                                {{ $competition->participants_count }} peserta · {{ $competition->finished_matches_count }}/{{ $competition->matches_count }} pertandingan
                                            </p>
                                        </div>
                                        <x-ui.badge :variant="match($competition->status->value) {
                                            'ongoing' => 'live',
                                            'completed' => 'success',
                                            'draft' => 'default',
                                            default => 'info',
                                        }">{{ $competition->status->label() }}</x-ui.badge>
                                    </div>
                                    <div class="mt-3">
                                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                            <span>Progress</span>
                                            <span>{{ $competition->progress }}%</span>
                                        </div>
                                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                                            <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $competition->progress }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.card>
            </div>

            {{-- Upcoming Matches --}}
            <div>
                <x-ui.card>
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-secondary dark:text-white">Pertandingan Mendatang</h2>
                        <x-ui.button variant="ghost" size="sm" :href="route('matches.index')">
                            <i data-lucide="arrow-right" class="h-4 w-4"></i>
                        </x-ui.button>
                    </div>

                    @if ($upcomingMatches->isEmpty())
                        <p class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">Tidak ada pertandingan terjadwal.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($upcomingMatches as $match)
                                <a href="{{ route('matches.show', $match) }}" class="block rounded-lg border border-slate-100 p-3 transition hover:border-primary/30 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700/30">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-medium text-primary">{{ $match->competition->name ?? '-' }}</span>
                                        <x-ui.badge :variant="$match->status->value === 'live' ? 'live' : 'info'" size="sm">
                                            {{ $match->status->label() }}
                                        </x-ui.badge>
                                    </div>
                                    <div class="mt-2 space-y-1">
                                        @foreach ($match->matchParticipants as $mp)
                                            <p class="text-sm text-secondary dark:text-slate-200">{{ $mp->participant->name ?? 'TBD' }}</p>
                                        @endforeach
                                        @if ($match->matchParticipants->isEmpty())
                                            <p class="text-sm text-slate-400">Peserta belum ditentukan</p>
                                        @endif
                                    </div>
                                    @if ($match->scheduled_at)
                                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                            <i data-lucide="clock" class="inline h-3 w-3"></i>
                                            {{ $match->scheduled_at->format('d M Y, H:i') }}
                                        </p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </x-ui.card>
            </div>
        </div>

        {{-- Recent Activity --}}
        <x-ui.card class="mt-6">
            <h2 class="mb-4 text-lg font-semibold text-secondary dark:text-white">Aktivitas Terbaru</h2>

            @if ($recentActivity->isEmpty())
                <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada aktivitas.</p>
            @else
                <div class="space-y-3">
                    @foreach ($recentActivity as $log)
                        <div class="flex items-start gap-3 rounded-lg border border-slate-100 p-3 dark:border-slate-700">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700">
                                <i data-lucide="activity" class="h-4 w-4 text-slate-500"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-secondary dark:text-slate-200">{{ $log->description }}</p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $log->user->name ?? 'Sistem' }} · {{ $log->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    @endif
</x-app-layout>
