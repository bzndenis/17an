@php
    $sides = $match->matchParticipants->sortBy('side')->groupBy(fn ($mp) => $mp->side ?? 1);
@endphp

<x-app-layout :title="'Pertandingan #' . $match->match_number">
    <x-ui.page-header :title="'Pertandingan #' . $match->match_number" :description="($match->competition->name ?? '') . ' · ' . ($match->round->name ?? '') . ' · ' . $match->competition->matchFormatLabel()">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('matches.index')">Kembali</x-ui.button>
            @if ($match->status->value !== 'finished')
                <x-ui.button type="button" @click="$dispatch('open-modal', 'match-result')">
                    <i data-lucide="clipboard-edit" class="h-4 w-4"></i>
                    Input Hasil
                </x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <x-ui.badge :variant="$match->status->value === 'live' ? 'live' : ($match->status->value === 'finished' ? 'success' : 'info')" size="lg">
                    {{ $match->status->label() }}
                </x-ui.badge>
                @if ($match->scheduled_at)
                    <span class="text-sm text-slate-500">{{ $match->scheduled_at->format('d M Y, H:i') }}</span>
                @endif
            </div>

            <div class="space-y-4">
                @forelse ($sides as $side => $members)
                    @php $isWinner = $members->contains(fn ($mp) => $mp->is_winner); @endphp
                    <div class="rounded-xl border p-4 {{ $isWinner ? 'border-primary bg-primary/5' : 'border-slate-200 dark:border-slate-700' }}">
                        <div class="mb-2 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if ($isWinner)
                                    <i data-lucide="crown" class="h-5 w-5 text-amber-500"></i>
                                @endif
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    {{ $members->count() > 1 ? 'Tim '.$side : 'Sisi '.$side }}
                                </span>
                            </div>
                            <span class="text-3xl font-bold font-mono {{ $isWinner ? 'text-primary' : 'text-secondary dark:text-white' }}">
                                {{ $members->first()->score ?? '-' }}
                            </span>
                        </div>
                        <ul class="space-y-1">
                            @foreach ($members as $mp)
                                <li>
                                    <a href="{{ route('participants.show', $mp->participant) }}" class="font-semibold hover:text-primary dark:text-white">
                                        {{ $mp->participant->name ?? 'TBD' }}
                                    </a>
                                    <span class="text-xs text-slate-500">· No. {{ $mp->participant->number ?? '-' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @if (! $loop->last)
                        <div class="text-center text-sm font-bold text-slate-400">VS</div>
                    @endif
                @empty
                    <p class="py-8 text-center text-slate-500">Peserta belum ditentukan.</p>
                @endforelse
            </div>

            @if ($match->result)
                <div class="mt-6 rounded-lg bg-slate-50 p-4 dark:bg-slate-900/50">
                    <p class="text-xs font-medium uppercase text-slate-500">Catatan</p>
                    <p class="mt-1 text-sm">{{ $match->result->notes ?? 'Tidak ada catatan.' }}</p>
                    @if ($match->result->finished_at)
                        <p class="mt-2 text-xs text-slate-400">Selesai: {{ $match->result->finished_at->format('d M Y, H:i') }}</p>
                    @endif
                </div>
            @endif
        </x-ui.card>

        <div class="space-y-4">
            <x-ui.card>
                <h3 class="mb-3 font-semibold text-secondary dark:text-white">Informasi</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500">Lomba</dt>
                        <dd><a href="{{ route('competitions.show', $match->competition) }}" class="font-medium hover:text-primary">{{ $match->competition->name }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Format</dt>
                        <dd class="font-medium">{{ $match->competition->matchFormatLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Babak</dt>
                        <dd class="font-medium">{{ $match->round->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Venue</dt>
                        <dd class="font-medium">{{ $match->venue ?? '-' }}</dd>
                    </div>
                    @if ($match->result?->winner)
                        <div>
                            <dt class="text-slate-500">Pemenang</dt>
                            <dd class="font-medium text-primary">
                                @php
                                    $winSide = $match->matchParticipants->firstWhere('is_winner', true)?->side;
                                    $winners = $match->matchParticipants->where('side', $winSide);
                                @endphp
                                {{ $winners->map(fn ($mp) => $mp->participant->name)->filter()->implode(' & ') ?: $match->result->winner->name }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>

            <x-ui.button variant="outline" class="w-full" :href="route('brackets.show', $match->competition)">
                <i data-lucide="git-branch" class="h-4 w-4"></i>
                Lihat Bracket
            </x-ui.button>
        </div>
    </div>

    <x-modal name="match-result" focusable>
        <form method="POST" action="{{ route('matches.result', $match) }}" class="p-6">
            @csrf
            <h2 class="text-lg font-semibold text-secondary dark:text-white">Input Hasil Pertandingan</h2>
            <p class="mt-1 text-sm text-slate-500">Masukkan skor per sisi / tim.</p>

            <div class="mt-6 space-y-4">
                @foreach ($sides as $side => $members)
                    <div>
                        <label class="form-label">
                            {{ $members->count() > 1 ? 'Tim '.$side : 'Sisi '.$side }}
                            <span class="font-normal text-slate-500">
                                ({{ $members->map(fn ($mp) => $mp->participant->name)->filter()->implode(', ') }})
                            </span>
                        </label>
                        <input
                            type="number"
                            name="side_scores[{{ $side }}]"
                            value="{{ old('side_scores.'.$side, $members->first()->score ?? 0) }}"
                            min="0"
                            class="form-input"
                            required
                        >
                    </div>
                @endforeach

                <div>
                    <label class="form-label">Pemenang (opsional)</label>
                    <select name="winner_side" class="form-select">
                        <option value="">Otomatis (skor tertinggi)</option>
                        @foreach ($sides as $side => $members)
                            <option value="{{ $side }}" @selected(old('winner_side') == $side)>
                                {{ $members->map(fn ($mp) => $mp->participant->name)->filter()->implode(' & ') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-textarea" rows="2">{{ old('notes', $match->result->notes ?? '') }}</textarea>
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="finish" value="1" class="rounded border-slate-300 text-primary focus:ring-primary" @checked(old('finish', true))>
                    <span class="text-sm">Tandai pertandingan selesai</span>
                </label>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-ui.button variant="outline" type="button" @click="$dispatch('close-modal', 'match-result')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan Hasil</x-ui.button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
