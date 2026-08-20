@php
    $groupRounds = $rounds->where('type', 'group')->values();
    $knockoutRounds = $rounds->where('type', 'knockout')->values();
    $groupCount = (int) ($competition->config['group_count'] ?? $groupEntries->count() ?: 2);
    $qualifyPerGroup = (int) ($competition->config['qualify_per_group'] ?? 2);
    $rankings = $competition->rankings->keyBy('participant_id');
@endphp

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-sm font-medium text-secondary dark:text-white">Fase Grup → Knockout</p>
        <p class="text-xs text-slate-500">{{ $groupCount }} grup · {{ $qualifyPerGroup }} lolos per grup</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if ($canRandomizeMatches ?? false)
            <form
                action="{{ route('brackets.randomize', $competition) }}"
                method="POST"
                onsubmit="return confirm('Acak ulang peserta ke grup? Bracket akan di-generate ulang jika sudah ada.')"
            >
                @csrf
                <x-ui.button variant="outline" type="submit" size="sm">
                    <i data-lucide="shuffle" class="h-4 w-4"></i>
                    Acak Peserta
                </x-ui.button>
            </form>
        @endif
        <form
            action="{{ route('brackets.generate', $competition) }}"
            method="POST"
            @if ($rounds->isNotEmpty())
                onsubmit="return confirm('Regenerate bracket grup + knockout? Data pertandingan lama akan diganti.')"
            @endif
        >
            @csrf
            <x-ui.button type="submit" size="sm">
                <i data-lucide="zap" class="h-4 w-4"></i>
                {{ $rounds->isEmpty() ? 'Generate Bracket' : 'Regenerate' }}
            </x-ui.button>
        </form>
    </div>
</div>

@if ($rounds->isEmpty())
    <x-ui.empty-state
        title="Bracket Belum Dibuat"
        description="Generate untuk membuat fase grup dan bracket eliminasi."
        icon="git-branch"
    />
@else
    <div class="mb-8 space-y-6">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Fase Grup</h3>

        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($groupRounds as $round)
                @php
                    $groupNum = $round->matches->first()?->bracket_position
                        ?? (ord(substr($round->name, -1)) - 64);
                    $entries = ($groupEntries[$groupNum] ?? collect())->sort(function ($a, $b) use ($rankings) {
                        $ra = $rankings[$a->participant_id] ?? null;
                        $rb = $rankings[$b->participant_id] ?? null;

                        return [$rb?->points ?? 0, $rb?->won ?? 0]
                            <=> [$ra?->points ?? 0, $ra?->won ?? 0];
                    })->values();
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="font-semibold">{{ $round->name }}</h4>
                        <span class="text-xs text-slate-500">{{ $round->matches->count() }} match</span>
                    </div>

                    <div class="mb-4 overflow-hidden rounded-lg border border-slate-100 dark:border-slate-700">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs text-slate-500 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-3 py-2">#</th>
                                    <th class="px-3 py-2">Peserta</th>
                                    <th class="px-3 py-2 text-center">M</th>
                                    <th class="px-3 py-2 text-center">Poin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($entries as $index => $entry)
                                    @php $rank = $rankings[$entry->participant_id] ?? null; @endphp
                                    <tr @class([
                                        'border-t border-slate-100 dark:border-slate-700',
                                        'bg-emerald-50/60 dark:bg-emerald-900/10' => $index < $qualifyPerGroup,
                                    ])>
                                        <td class="px-3 py-2 font-mono text-xs">{{ $index + 1 }}</td>
                                        <td class="px-3 py-2 font-medium">{{ $entry->participant->name ?? '-' }}</td>
                                        <td class="px-3 py-2 text-center">{{ $rank?->played ?? 0 }}</td>
                                        <td class="px-3 py-2 text-center font-semibold">{{ $rank?->points ?? 0 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-3 text-center text-slate-400">Belum ada peserta</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-2">
                        @foreach ($round->matches->sortBy('match_number') as $match)
                            @include('brackets.partials.match-card', ['match' => $match, 'isFinal' => false])
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Fase Knockout</h3>
        <p class="text-xs text-slate-500">Slot terisi otomatis setelah semua pertandingan grup selesai.</p>
        @include('brackets.partials.visual', [
            'rounds' => $knockoutRounds,
            'competition' => $competition,
            'showGenerate' => false,
            'canRandomizeMatches' => false,
        ])
    </div>
@endif
