@if ($rounds->isEmpty())
    <x-ui.empty-state
        title="Bracket Belum Dibuat"
        description="Generate bracket untuk memulai pertandingan knockout."
        icon="git-branch"
    >
        @if ($showGenerate ?? true)
            <x-slot:action>
                <form action="{{ route('brackets.generate', $competition) }}" method="POST">
                    @csrf
                    <x-ui.button type="submit">
                        <i data-lucide="zap" class="h-4 w-4"></i>
                        Generate Bracket
                    </x-ui.button>
                </form>
            </x-slot:action>
        @endif
    </x-ui.empty-state>
@else
    @if ($showGenerate ?? true)
        <div class="mb-4 flex justify-end">
            <form action="{{ route('brackets.generate', $competition) }}" method="POST" onsubmit="return confirm('Regenerate bracket? Data pertandingan lama akan diganti.')">
                @csrf
                <x-ui.button variant="outline" size="sm" type="submit">
                    <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                    Regenerate
                </x-ui.button>
            </form>
        </div>
    @endif

    <div class="overflow-x-auto pb-4">
        <div class="flex gap-8 min-w-max px-4">
            @foreach ($rounds as $round)
                <div class="bracket-round">
                    <h3 class="mb-2 text-center text-xs font-bold uppercase tracking-wider text-slate-500">{{ $round->name }}</h3>
                    @foreach ($round->matches as $match)
                        <div class="bracket-match">
                            <div class="mb-2 flex items-center justify-between text-xs text-slate-400">
                                <span>#{{ $match->match_number }}</span>
                                <x-ui.badge :variant="$match->status->value === 'finished' ? 'success' : ($match->status->value === 'live' ? 'live' : 'default')" size="sm">
                                    {{ $match->status->label() }}
                                </x-ui.badge>
                            </div>
                            @forelse ($match->matchParticipants as $mp)
                                <div class="bracket-participant {{ $mp->is_winner ? 'bracket-participant-winner' : '' }}">
                                    <span class="truncate">{{ $mp->participant->name ?? 'TBD' }}</span>
                                    <span class="ml-2 font-mono font-bold">{{ $mp->score ?? '-' }}</span>
                                </div>
                            @empty
                                <div class="bracket-participant text-slate-400">Menunggu peserta...</div>
                                <div class="bracket-participant text-slate-400">Menunggu peserta...</div>
                            @endforelse
                            <div class="mt-2 text-right">
                                <a href="{{ route('matches.show', $match) }}" class="text-xs text-primary hover:underline">Detail →</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endif
