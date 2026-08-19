@php
    $allMatches = $rounds->flatMap(fn($r) => $r->matches)->sortBy('match_number');
    $totalMatches = $allMatches->count();
    $finishedMatches = $allMatches->filter(fn($m) => $m->status->value === 'finished')->count();
    $progress = $totalMatches > 0 ? round(($finishedMatches / $totalMatches) * 100) : 0;
@endphp

@if ($allMatches->isEmpty())
    <x-ui.empty-state
        title="Pertandingan Belum Dibuat"
        description="Generate pertandingan round-robin untuk sistem poin."
        icon="list"
    >
        <x-slot:action>
            <form action="{{ route('brackets.generate', $competition) }}" method="POST">
                @csrf
                <x-ui.button type="submit">
                    <i data-lucide="zap" class="h-4 w-4"></i>
                    Generate Pertandingan
                </x-ui.button>
            </form>
        </x-slot:action>
    </x-ui.empty-state>
@else
    <div class="space-y-6">
        {{-- Progress bar --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-4 text-sm">
                    <span class="font-semibold">{{ $finishedMatches }}/{{ $totalMatches }}</span>
                    <span class="text-slate-500">Pertandingan Selesai</span>
                    <span class="font-semibold">{{ $progress }}%</span>
                </div>
                <form action="{{ route('brackets.generate', $competition) }}" method="POST" onsubmit="return confirm('Regenerate semua pertandingan? Data lama akan diganti.')">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700 flex items-center gap-1">
                        <i data-lucide="refresh-cw" class="h-3.5 w-3.5"></i> Regenerate
                    </button>
                </form>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2">
                <div class="bg-primary-500 h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
            </div>
        </div>

        {{-- Match list --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($allMatches as $match)
                @php
                    $participants = $match->matchParticipants->sortBy('id');
                    $home = $participants->first();
                    $away = $participants->last();
                    $isFinished = $match->status->value === 'finished';
                    $isLive = $match->status->value === 'live';
                @endphp
                <div @class([
                    'rounded-xl border p-4 transition-all',
                    'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700' => !$isFinished && !$isLive,
                    'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' => $isFinished,
                    'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-800 ring-2 ring-red-300' => $isLive,
                ])>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-mono text-slate-400">#{{ $match->match_number }}</span>
                        <x-ui.badge :variant="$isFinished ? 'success' : ($isLive ? 'danger' : 'secondary')" size="sm">
                            {{ $isFinished ? 'Selesai' : ($isLive ? 'Live' : 'Terjadwal') }}
                        </x-ui.badge>
                    </div>

                    <div class="space-y-2">
                        @foreach ([$home, $away] as $mp)
                            @if ($mp && $mp->participant)
                                <div @class([
                                    'flex items-center justify-between py-1.5 px-2 rounded-lg',
                                    'bg-amber-50 dark:bg-amber-900/20 font-semibold' => $isFinished && $mp->is_winner,
                                ])>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-xs font-bold text-primary-600">
                                            {{ mb_strtoupper(mb_substr($mp->participant->name, 0, 1)) }}
                                        </div>
                                        <span class="text-sm">{{ $mp->participant->name }}</span>
                                        @if ($isFinished && $mp->is_winner)
                                            <i data-lucide="crown" class="h-3.5 w-3.5 text-amber-500"></i>
                                        @endif
                                    </div>
                                    <span class="text-sm font-mono font-bold">{{ $mp->score }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if ($match->scheduled_at)
                        <div class="mt-2 text-xs text-slate-400 flex items-center gap-1">
                            <i data-lucide="clock" class="h-3 w-3"></i>
                            {{ $match->scheduled_at->format('d M, H:i') }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
