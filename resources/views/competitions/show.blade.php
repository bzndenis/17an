<x-app-layout :title="$competition->name">
    <x-ui.page-header :title="$competition->name" :description="$competition->system->label() . ' · ' . ($competition->category ?? 'Umum')">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('competitions.index')">Kembali</x-ui.button>
            <x-ui.button variant="outline" :href="route('competitions.wizard', ['competition' => $competition, 'step' => 2])">
                <i data-lucide="wand-2" class="h-4 w-4"></i>
                Wizard
            </x-ui.button>
            <x-ui.button :href="route('competitions.edit', $competition)">
                <i data-lucide="pencil" class="h-4 w-4"></i>
                Edit
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Banner & meta --}}
    @if ($competition->banner)
        <div class="mb-6 overflow-hidden rounded-xl">
            <img src="{{ Storage::url($competition->banner) }}" alt="" class="h-48 w-full object-cover">
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <x-ui.badge :variant="match($competition->status->value) {
            'ongoing' => 'live',
            'completed' => 'success',
            default => 'default',
        }">{{ $competition->status->label() }}</x-ui.badge>
        @if ($competition->location)
            <span class="text-sm text-slate-500"><i data-lucide="map-pin" class="inline h-4 w-4"></i> {{ $competition->location }}</span>
        @endif
        @if ($competition->start_at)
            <span class="text-sm text-slate-500"><i data-lucide="calendar" class="inline h-4 w-4"></i> {{ $competition->start_at->format('d M Y, H:i') }}</span>
        @endif
    </div>

    {{-- Tabs --}}
    @php
        $tabs = [
            'overview' => 'Ringkasan',
            'participants' => 'Peserta',
            'matches' => 'Pertandingan',
            'bracket' => 'Bracket',
            'ranking' => 'Peringkat',
        ];
    @endphp

    <div class="mb-6 flex gap-1 overflow-x-auto rounded-lg border border-slate-200 bg-white p-1 dark:border-slate-700 dark:bg-slate-800">
        @foreach ($tabs as $key => $label)
            <a
                href="{{ route('competitions.show', ['competition' => $competition, 'tab' => $key]) }}"
                class="whitespace-nowrap rounded-md px-4 py-2 text-sm font-medium transition {{ $tab === $key ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Tab Content --}}
    @if ($tab === 'overview')
        <div class="grid gap-4 sm:grid-cols-3 mb-6">
            <x-ui.stat-card label="Peserta" :value="$stats['participants_count'] ?? 0" icon="users" color="primary" />
            <x-ui.stat-card label="Pertandingan" :value="$stats['matches_count'] ?? 0" icon="swords" color="blue" />
            <x-ui.stat-card label="Selesai" :value="$stats['finished_matches'] ?? 0" icon="check-circle" color="green" />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.card>
                <h3 class="mb-3 font-semibold text-secondary dark:text-white">Deskripsi</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $competition->description ?? 'Tidak ada deskripsi.' }}</p>
            </x-ui.card>
            <x-ui.card>
                <h3 class="mb-3 font-semibold text-secondary dark:text-white">Hadiah</h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2"><span class="text-amber-500">🥇</span> {{ $competition->prize_1 ?? '-' }}</li>
                    <li class="flex items-center gap-2"><span class="text-slate-400">🥈</span> {{ $competition->prize_2 ?? '-' }}</li>
                    <li class="flex items-center gap-2"><span class="text-amber-700">🥉</span> {{ $competition->prize_3 ?? '-' }}</li>
                </ul>
            </x-ui.card>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <x-ui.button :href="route('brackets.show', $competition)">Lihat Bracket</x-ui.button>
            <x-ui.button variant="outline" :href="route('rankings.competition', $competition)">Lihat Peringkat</x-ui.button>
        </div>

    @elseif ($tab === 'participants')
        @if (($participants ?? collect())->isEmpty())
            <x-ui.empty-state title="Belum Ada Peserta" description="Gunakan wizard untuk mendaftarkan peserta." icon="users" :actionHref="route('competitions.wizard', ['competition' => $competition, 'step' => 2])" actionLabel="Buka Wizard" />
        @else
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Seed</th>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Tim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($participants as $entry)
                            <tr>
                                <td class="font-mono">{{ $entry->seed }}</td>
                                <td class="font-mono">{{ $entry->participant->number }}</td>
                                <td>
                                    <a href="{{ route('participants.show', $entry->participant) }}" class="font-medium hover:text-primary">
                                        {{ $entry->participant->name }}
                                    </a>
                                </td>
                                <td>{{ $entry->participant->category->name ?? '-' }}</td>
                                <td>{{ $entry->participant->team ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    @elseif ($tab === 'matches')
        @if (($matches ?? collect())->isEmpty())
            <x-ui.empty-state title="Belum Ada Pertandingan" description="Generate bracket untuk membuat jadwal pertandingan." icon="swords" :actionHref="route('brackets.show', $competition)" actionLabel="Ke Bracket" />
        @else
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ $matches->count() }} pertandingan
                    @if ($canRandomizeMatches ?? false)
                        · Urutan pasangan bisa diacak ulang sebelum pertandingan dimulai
                    @endif
                </p>
                @if ($canRandomizeMatches ?? false)
                    <form
                        action="{{ route('competitions.matches.randomize', $competition) }}"
                        method="POST"
                        onsubmit="return confirm('Random ulang semua pasangan pertandingan? Hasil dan penempatan peserta akan direset.')"
                    >
                        @csrf
                        <x-ui.button variant="outline" type="submit">
                            <i data-lucide="shuffle" class="h-4 w-4"></i>
                            Random Ulang Pasangan
                        </x-ui.button>
                    </form>
                @else
                    <span class="text-xs text-slate-400" title="Tidak bisa random ulang jika ada pertandingan selesai/live">
                        <i data-lucide="lock" class="inline h-3.5 w-3.5"></i>
                        Random ulang terkunci (ada pertandingan berjalan/selesai)
                    </span>
                @endif
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Babak</th>
                            <th>Peserta</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($matches as $match)
                            <tr>
                                <td class="font-mono">{{ $match->match_number }}</td>
                                <td>{{ $match->round->name ?? '-' }}</td>
                                <td>
                                    @foreach ($match->matchParticipants as $mp)
                                        <span class="{{ $mp->is_winner ? 'font-semibold text-primary' : '' }}">{{ $mp->participant->name ?? 'TBD' }}</span>
                                        @if (!$loop->last) <span class="text-slate-400"> vs </span> @endif
                                    @endforeach
                                </td>
                                <td>
                                    <x-ui.badge :variant="$match->status->value === 'live' ? 'live' : ($match->status->value === 'finished' ? 'success' : 'default')">
                                        {{ $match->status->label() }}
                                    </x-ui.badge>
                                </td>
                                <td class="text-right">
                                    <x-ui.button variant="ghost" size="sm" :href="route('matches.show', $match)">Detail</x-ui.button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    @elseif ($tab === 'bracket')
        @if ($competition->system === \App\Enums\CompetitionSystem::Point)
            @include('brackets.partials.point-system', ['rounds' => $rounds ?? collect(), 'competition' => $competition])
        @else
            @include('brackets.partials.visual', ['rounds' => $rounds ?? collect(), 'competition' => $competition, 'showGenerate' => false])
        @endif

    @elseif ($tab === 'ranking')
        @if (($rankings ?? collect())->isEmpty())
            <x-ui.empty-state title="Belum Ada Peringkat" description="Peringkat akan muncul setelah pertandingan selesai." icon="medal" />
        @else
            @include('rankings.partials.podium', ['leaderboard' => $rankings])
            @include('rankings.partials.table', ['leaderboard' => $rankings, 'showCompetition' => false])
        @endif
    @endif
</x-app-layout>
