@php
    $participants = $match->matchParticipants->sortBy('side')->values();
    $sides = $participants->groupBy(fn ($mp) => $mp->side ?? 1);
    $isLive = $match->status->value === 'live';
    $isFinished = $match->status->value === 'finished';
    $winner = $participants->firstWhere('is_winner', true);
    $emptyLabel = 'TBD';
    $emptyTitle = 'Menunggu peserta';
@endphp

<a
    href="{{ route('matches.show', $match) }}"
    class="bracket-match group {{ $isFinal ?? false ? 'bracket-match--final' : '' }} {{ $isLive ? 'bracket-match--live' : '' }}"
    title="Match #{{ $match->match_number }}"
>
    <div class="bracket-match__header">
        <span class="bracket-match__number">#{{ $match->match_number }}</span>
        @if ($isLive)
            <span class="bracket-match__live">
                <span class="bracket-match__live-dot"></span>
                LIVE
            </span>
        @else
            <x-ui.badge
                :variant="$isFinished ? 'success' : ($match->status->value === 'cancelled' ? 'danger' : 'default')"
                size="sm"
            >
                {{ $match->status->label() }}
            </x-ui.badge>
        @endif
    </div>

    <div class="bracket-match__body">
        @forelse ($sides as $side => $members)
            @php
                $isWinner = $members->contains(fn ($mp) => $mp->is_winner);
                $names = $members->map(fn ($mp) => $mp->participant->name ?? null)->filter()->values();
                $label = $names->isNotEmpty() ? $names->implode(' & ') : null;
                $initial = $label ? mb_strtoupper(mb_substr($names->first(), 0, 1)) : '?';
                $score = $members->first()->score ?? 0;
            @endphp
            <div @class([
                'bracket-match__row',
                'bracket-match__row--winner' => $isWinner,
                'bracket-match__row--loser' => $isFinished && ! $isWinner && $winner,
                'bracket-match__row--empty' => ! $label,
            ])>
                <div class="bracket-match__player">
                    <span @class([
                        'bracket-match__avatar',
                        'bracket-match__avatar--winner' => $isWinner,
                    ])>{{ $initial }}</span>
                    <span class="bracket-match__name" @if(! $label) title="{{ $emptyTitle }}" @endif>
                        {{ $label ?? $emptyLabel }}
                    </span>
                    @if ($isWinner && ($isFinal ?? false))
                        <i data-lucide="trophy" class="bracket-match__trophy h-3.5 w-3.5"></i>
                    @endif
                </div>
                <span @class([
                    'bracket-match__score',
                    'bracket-match__score--winner' => $isWinner,
                ])>
                    {{ $label ? $score : '–' }}
                </span>
            </div>
        @empty
            @for ($i = 0; $i < 2; $i++)
                <div class="bracket-match__row bracket-match__row--empty">
                    <div class="bracket-match__player">
                        <span class="bracket-match__avatar">?</span>
                        <span class="bracket-match__name" title="{{ $emptyTitle }}">{{ $emptyLabel }}</span>
                    </div>
                    <span class="bracket-match__score">–</span>
                </div>
            @endfor
        @endforelse
    </div>

    @if ($match->scheduled_at)
        <div class="bracket-match__footer">
            <span class="bracket-match__meta">
                <i data-lucide="clock" class="h-3 w-3"></i>
                {{ $match->scheduled_at->format('d M, H:i') }}
            </span>
        </div>
    @endif
</a>
