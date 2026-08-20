@if ($rounds->isEmpty())
    <x-ui.empty-state
        title="Bracket Belum Dibuat"
        description="Generate bracket untuk memulai pertandingan knockout."
        icon="git-branch"
    >
        @if (($showGenerate ?? true) || ($canRandomizeMatches ?? false))
            <x-slot:action>
                <div class="flex flex-wrap justify-center gap-2">
                    @if ($canRandomizeMatches ?? false)
                        <form action="{{ route('brackets.randomize', $competition) }}" method="POST" onsubmit="return confirm('Acak ulang urutan peserta?')">
                            @csrf
                            <x-ui.button variant="outline" type="submit">
                                <i data-lucide="shuffle" class="h-4 w-4"></i>
                                Acak Peserta
                            </x-ui.button>
                        </form>
                    @endif
                    @if ($showGenerate ?? true)
                        <form action="{{ route('brackets.generate', $competition) }}" method="POST">
                            @csrf
                            <x-ui.button type="submit">
                                <i data-lucide="zap" class="h-4 w-4"></i>
                                Generate Bracket
                            </x-ui.button>
                        </form>
                    @endif
                </div>
            </x-slot:action>
        @endif
    </x-ui.empty-state>
@else
    @php
        $totalRounds = $rounds->count();
        $totalMatches = $rounds->sum(fn ($r) => $r->matches->count());
        $finishedMatches = $rounds->sum(fn ($r) => $r->matches->filter(fn ($m) => $m->status->value === 'finished')->count());
        $liveMatches = $rounds->sum(fn ($r) => $r->matches->filter(fn ($m) => $m->status->value === 'live')->count());
        $progress = $totalMatches > 0 ? round(($finishedMatches / $totalMatches) * 100) : 0;

        $finalMatch = $rounds->last()?->matches->first();
        $champion = $finalMatch?->matchParticipants->firstWhere('is_winner', true)?->participant;

        $matchHeight = 132;
        $slotHeight = 168;
        $connectorWidth = 48;
        $columnWidth = 280;
        $treePadding = 32;

        // Anchor round = round with most matches (usually the earliest full round)
        $anchorRoundIndex = 0;
        $maxMatches = 0;
        foreach ($rounds as $ri => $r) {
            if ($r->matches->count() > $maxMatches) {
                $maxMatches = $r->matches->count();
                $anchorRoundIndex = $ri;
            }
        }

        $treeHeight = ($maxMatches - 1) * $slotHeight + $matchHeight + $treePadding;

        // Build match ID → position map using next_match_id relationships
        $matchPositions = []; // match_id => top position

        // Position anchor round evenly
        $anchorRound = $rounds[$anchorRoundIndex];
        foreach ($anchorRound->matches as $mi => $match) {
            $top = $mi * $slotHeight;
            $matchPositions[$match->id] = $top;
        }

        // Position rounds after anchor (each match centered between its two feeder matches)
        for ($ri = $anchorRoundIndex + 1; $ri < $totalRounds; $ri++) {
            $prevRound = $rounds[$ri - 1];
            foreach ($rounds[$ri]->matches as $mi => $match) {
                $feeders = $prevRound->matches->filter(fn($m) => $m->next_match_id === $match->id);
                if ($feeders->count() >= 2) {
                    $tops = $feeders->map(fn($m) => $matchPositions[$m->id] ?? 0);
                    $matchPositions[$match->id] = ($tops->min() + $tops->max()) / 2;
                } elseif ($feeders->count() === 1) {
                    $matchPositions[$match->id] = $matchPositions[$feeders->first()->id] ?? $mi * $slotHeight;
                } else {
                    $matchPositions[$match->id] = $mi * $slotHeight * pow(2, $ri - $anchorRoundIndex);
                }
            }
        }

        // Position rounds before anchor (R1 matches placed at the center of their target in anchor round)
        for ($ri = $anchorRoundIndex - 1; $ri >= 0; $ri--) {
            foreach ($rounds[$ri]->matches as $mi => $match) {
                if ($match->next_match_id && isset($matchPositions[$match->next_match_id])) {
                    $targetTop = $matchPositions[$match->next_match_id];
                    $siblings = $rounds[$ri]->matches->filter(fn($m) => $m->next_match_id === $match->next_match_id);
                    $siblingIndex = $siblings->values()->search(fn($m) => $m->id === $match->id);
                    $siblingCount = $siblings->count();
                    if ($siblingCount === 2) {
                        $offset = ($siblingIndex === 0 ? -1 : 1) * $slotHeight * 0.5;
                        $matchPositions[$match->id] = $targetTop + $offset;
                    } else {
                        $matchPositions[$match->id] = $targetTop;
                    }
                } else {
                    $matchPositions[$match->id] = $mi * $slotHeight;
                }
            }
        }

        $positions = [];
        foreach ($rounds as $roundIndex => $round) {
            foreach ($round->matches as $matchIndex => $match) {
                $top = $matchPositions[$match->id] ?? 0;
                $positions[$roundIndex][$matchIndex] = [
                    'top' => $top,
                    'center' => $top + $matchHeight / 2,
                ];
            }
        }
    @endphp

    <div
        x-data="{
            zoom: 100,
            fullscreen: false,
            zoomIn() { this.zoom = Math.min(this.zoom + 10, 150) },
            zoomOut() { this.zoom = Math.max(this.zoom - 10, 60) },
            resetZoom() { this.zoom = 100 },
            toggleFullscreen() {
                this.fullscreen = !this.fullscreen;
                if (this.fullscreen) { this.$refs.viewer.requestFullscreen?.(); }
                else { document.exitFullscreen?.(); }
            }
        }"
        x-ref="viewer"
        @fullscreenchange.window="fullscreen = !!document.fullscreenElement"
        class="bracket-viewer"
        :class="{ 'bracket-viewer--fullscreen': fullscreen }"
    >
        <div class="bracket-toolbar">
            <div class="bracket-toolbar__stats">
                <div class="bracket-stat">
                    <span class="bracket-stat__value">{{ $finishedMatches }}/{{ $totalMatches }}</span>
                    <span class="bracket-stat__label">Selesai</span>
                </div>
                @if ($liveMatches > 0)
                    <div class="bracket-stat bracket-stat--live">
                        <span class="bracket-stat__value">{{ $liveMatches }}</span>
                        <span class="bracket-stat__label">Live</span>
                    </div>
                @endif
                <div class="bracket-stat">
                    <span class="bracket-stat__value">{{ $progress }}%</span>
                    <span class="bracket-stat__label">Progress</span>
                </div>
            </div>
            <div class="bracket-toolbar__progress">
                <div class="bracket-progress-track">
                    <div class="bracket-progress-fill" style="width: {{ $progress }}%"></div>
                </div>
            </div>
            <div class="bracket-toolbar__actions">
                <button type="button" @click="zoomOut()" class="bracket-tool-btn" title="Zoom out"><i data-lucide="zoom-out" class="h-4 w-4"></i></button>
                <button type="button" @click="resetZoom()" class="bracket-tool-btn bracket-tool-btn--label"><span x-text="zoom + '%'"></span></button>
                <button type="button" @click="zoomIn()" class="bracket-tool-btn" title="Zoom in"><i data-lucide="zoom-in" class="h-4 w-4"></i></button>
                <button type="button" @click="toggleFullscreen()" class="bracket-tool-btn" title="Fullscreen"><i data-lucide="maximize-2" class="h-4 w-4"></i></button>
                @if ($canRandomizeMatches ?? false)
                    <form action="{{ route('brackets.randomize', $competition) }}" method="POST" onsubmit="return confirm('Acak ulang peserta? Seed dan pasangan akan diganti.')">
                        @csrf
                        <button type="submit" class="bracket-tool-btn" title="Acak peserta"><i data-lucide="shuffle" class="h-4 w-4"></i></button>
                    </form>
                @endif
                @if ($showGenerate ?? true)
                    <form action="{{ route('brackets.generate', $competition) }}" method="POST" onsubmit="return confirm('Regenerate & acak pasangan? Data pertandingan lama akan diganti.')">
                        @csrf
                        <button type="submit" class="bracket-tool-btn bracket-tool-btn--danger" title="Regenerate"><i data-lucide="refresh-cw" class="h-4 w-4"></i></button>
                    </form>
                @endif
            </div>
        </div>

        <div class="bracket-scroll">
            <div
                class="bracket-canvas"
                :style="`transform: scale(${zoom / 100}); transform-origin: top left;`"
            >
                {{-- Header row --}}
                <div class="bracket-headers">
                    @foreach ($rounds as $roundIndex => $round)
                        <div @class(['bracket-header', 'bracket-header--final' => $roundIndex === $totalRounds - 1]) style="width: {{ $columnWidth }}px">
                            <span class="bracket-header__title">{{ $round->name }}</span>
                            <span class="bracket-header__meta">{{ $round->matches->count() }} match</span>
                        </div>
                        @if ($roundIndex < $totalRounds - 1)
                            <div class="bracket-header-spacer" style="width: {{ $connectorWidth }}px"></div>
                        @endif
                    @endforeach
                    <div class="bracket-header-spacer" style="width: {{ $connectorWidth }}px"></div>
                    <div class="bracket-header bracket-header--champion" style="width: {{ $columnWidth }}px">
                        <span class="bracket-header__title">Juara</span>
                        <span class="bracket-header__meta">🏆 Champion</span>
                    </div>
                </div>

                {{-- Tree body --}}
                <div class="bracket-tree" style="height: {{ $treeHeight }}px;">
                    @foreach ($rounds as $roundIndex => $round)
                        @php $isLastRound = $roundIndex === $totalRounds - 1; @endphp

                        {{-- Match column --}}
                        <div class="bracket-col" style="width: {{ $columnWidth }}px; height: {{ $treeHeight }}px;">
                            @foreach ($round->matches as $matchIndex => $match)
                                <div
                                    class="bracket-slot"
                                    style="top: {{ $positions[$roundIndex][$matchIndex]['top'] }}px;"
                                >
                                    @include('brackets.partials.match-card', [
                                        'match' => $match,
                                        'isFinal' => $isLastRound,
                                    ])
                                </div>
                            @endforeach
                        </div>

                        {{-- Connector SVG to next round --}}
                        @if (! $isLastRound)
                            @php
                                $nextRound = $rounds[$roundIndex + 1];
                            @endphp
                            <div class="bracket-connector" style="width: {{ $connectorWidth }}px; height: {{ $treeHeight }}px;">
                                <svg width="{{ $connectorWidth }}" height="{{ $treeHeight }}" class="bracket-connector__svg">
                                    @foreach ($nextRound->matches as $nmi => $nextMatch)
                                        @php
                                            $feeders = $round->matches->filter(fn($m) => $m->next_match_id === $nextMatch->id)->values();
                                            $nextY = $positions[$roundIndex + 1][$nmi]['center'];
                                            $midX = $connectorWidth / 2;
                                        @endphp

                                        @if ($feeders->count() >= 2)
                                            @php
                                                $fi0 = $round->matches->values()->search(fn($m) => $m->id === $feeders[0]->id);
                                                $fi1 = $round->matches->values()->search(fn($m) => $m->id === $feeders[1]->id);
                                                $yA = $positions[$roundIndex][$fi0]['center'];
                                                $yB = $positions[$roundIndex][$fi1]['center'];
                                                $yMid = ($yA + $yB) / 2;
                                            @endphp
                                            <line x1="0" y1="{{ $yA }}" x2="{{ $midX }}" y2="{{ $yA }}" class="bracket-connector__line"/>
                                            <line x1="0" y1="{{ $yB }}" x2="{{ $midX }}" y2="{{ $yB }}" class="bracket-connector__line"/>
                                            <line x1="{{ $midX }}" y1="{{ $yA }}" x2="{{ $midX }}" y2="{{ $yB }}" class="bracket-connector__line"/>
                                            <line x1="{{ $midX }}" y1="{{ $yMid }}" x2="{{ $connectorWidth }}" y2="{{ $yMid }}" class="bracket-connector__line"/>
                                        @elseif ($feeders->count() === 1)
                                            @php
                                                $fi0 = $round->matches->values()->search(fn($m) => $m->id === $feeders[0]->id);
                                                $yA = $positions[$roundIndex][$fi0]['center'];
                                            @endphp
                                            <line x1="0" y1="{{ $yA }}" x2="{{ $connectorWidth }}" y2="{{ $nextY }}" class="bracket-connector__line"/>
                                        @endif
                                    @endforeach
                                </svg>
                            </div>
                        @endif
                    @endforeach

                    @php $championTop = ($treeHeight - $matchHeight) / 2; @endphp

                    {{-- Connector Final → Juara --}}
                    @if ($totalRounds > 0)
                        @php
                            $finalCenter = $positions[$totalRounds - 1][0]['center'];
                            $championCenter = $championTop + $matchHeight / 2;
                        @endphp
                        <div class="bracket-connector" style="width: {{ $connectorWidth }}px; height: {{ $treeHeight }}px;">
                            <svg width="{{ $connectorWidth }}" height="{{ $treeHeight }}">
                                <line x1="0" y1="{{ $finalCenter }}" x2="{{ $connectorWidth }}" y2="{{ $championCenter }}" class="bracket-connector__line"/>
                            </svg>
                        </div>
                    @endif

                    {{-- Champion column --}}
                    <div class="bracket-col bracket-col--champion" style="width: {{ $columnWidth }}px; height: {{ $treeHeight }}px;">
                        <div class="bracket-slot" style="top: {{ $championTop }}px;">
                            <div class="bracket-champion-card">
                                @if ($champion)
                                    <div class="bracket-champion-card__icon"><i data-lucide="crown" class="h-6 w-6 text-amber-400"></i></div>
                                    <div class="bracket-champion-card__avatar">{{ mb_strtoupper(mb_substr($champion->name, 0, 1)) }}</div>
                                    <p class="bracket-champion-card__name">{{ $champion->name }}</p>
                                    <p class="bracket-champion-card__label">Juara 1</p>
                                @else
                                    <div class="bracket-champion-card__empty">
                                        <i data-lucide="trophy" class="h-8 w-8 text-slate-400"></i>
                                        <p>Belum ditentukan</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bracket-legend">
            <span class="bracket-legend__item"><span class="bracket-legend__dot bracket-legend__dot--winner"></span> Pemenang</span>
            <span class="bracket-legend__item"><span class="bracket-legend__dot bracket-legend__dot--live"></span> Live</span>
            <span class="bracket-legend__item"><span class="bracket-legend__dot bracket-legend__dot--scheduled"></span> Terjadwal</span>
            <span class="bracket-legend__item"><span class="bracket-legend__dot bracket-legend__dot--finished"></span> Selesai</span>
        </div>
    </div>
@endif
