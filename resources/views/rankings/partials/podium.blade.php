@if ($leaderboard->count() >= 1)
    <div class="mb-8 flex items-end justify-center gap-4 px-4">
        @php
            $podiumOrder = [
                2 => ['height' => 'h-28', 'medal' => '🥈', 'ring' => 'ring-slate-300'],
                1 => ['height' => 'h-36', 'medal' => '🥇', 'ring' => 'ring-amber-400'],
                3 => ['height' => 'h-24', 'medal' => '🥉', 'ring' => 'ring-amber-700'],
            ];
        @endphp
        @foreach ([2, 1, 3] as $rank)
            @php $entry = $leaderboard->get($rank - 1); @endphp
            @if ($entry)
                <div class="flex flex-col items-center {{ 'podium-' . $rank }} w-32 sm:w-40">
                    <div class="mb-2 text-2xl">{{ $podiumOrder[$rank]['medal'] }}</div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-lg font-bold text-primary ring-4 {{ $podiumOrder[$rank]['ring'] }}">
                        {{ strtoupper(substr($entry->participant->name ?? '?', 0, 1)) }}
                    </div>
                    <p class="mt-2 text-center text-sm font-semibold text-secondary dark:text-white line-clamp-2">{{ $entry->participant->name ?? '-' }}</p>
                    <p class="text-lg font-bold text-primary">{{ $entry->points ?? 0 }} pts</p>
                    <div class="{{ $podiumOrder[$rank]['height'] }} w-full mt-3 rounded-t-xl bg-gradient-to-t from-primary/20 to-primary/5 flex items-center justify-center">
                        <span class="text-3xl font-bold text-primary/60">#{{ $rank }}</span>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif
