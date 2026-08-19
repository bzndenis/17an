<x-app-layout :title="'Pertandingan #' . $match->match_number">
    <x-ui.page-header :title="'Pertandingan #' . $match->match_number" :description="($match->competition->name ?? '') . ' · ' . ($match->round->name ?? '')">
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
        {{-- Match card --}}
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
                @foreach ($match->matchParticipants as $mp)
                    <div class="flex items-center justify-between rounded-xl border p-4 {{ $mp->is_winner ? 'border-primary bg-primary/5' : 'border-slate-200 dark:border-slate-700' }}">
                        <div class="flex items-center gap-4">
                            @if ($mp->is_winner)
                                <i data-lucide="crown" class="h-5 w-5 text-amber-500"></i>
                            @endif
                            <div>
                                <a href="{{ route('participants.show', $mp->participant) }}" class="text-lg font-semibold hover:text-primary dark:text-white">
                                    {{ $mp->participant->name ?? 'TBD' }}
                                </a>
                                <p class="text-xs text-slate-500">No. {{ $mp->participant->number ?? '-' }}</p>
                            </div>
                        </div>
                        <span class="text-3xl font-bold font-mono {{ $mp->is_winner ? 'text-primary' : 'text-secondary dark:text-white' }}">
                            {{ $mp->score ?? '-' }}
                        </span>
                    </div>
                    @if (!$loop->last)
                        <div class="text-center text-sm font-bold text-slate-400">VS</div>
                    @endif
                @endforeach

                @if ($match->matchParticipants->isEmpty())
                    <p class="py-8 text-center text-slate-500">Peserta belum ditentukan.</p>
                @endif
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

        {{-- Info sidebar --}}
        <div class="space-y-4">
            <x-ui.card>
                <h3 class="mb-3 font-semibold text-secondary dark:text-white">Informasi</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500">Lomba</dt>
                        <dd><a href="{{ route('competitions.show', $match->competition) }}" class="font-medium hover:text-primary">{{ $match->competition->name }}</a></dd>
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
                            <dd class="font-medium text-primary">{{ $match->result->winner->name }}</dd>
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

    {{-- Result Modal --}}
    <x-modal name="match-result" focusable>
        <form method="POST" action="{{ route('matches.result', $match) }}" class="p-6">
            @csrf
            <h2 class="text-lg font-semibold text-secondary dark:text-white">Input Hasil Pertandingan</h2>
            <p class="mt-1 text-sm text-slate-500">Masukkan skor untuk setiap peserta.</p>

            <div class="mt-6 space-y-4">
                @foreach ($match->matchParticipants as $mp)
                    <div>
                        <label class="form-label">{{ $mp->participant->name ?? 'Peserta' }}</label>
                        <input
                            type="number"
                            name="scores[{{ $mp->participant_id }}]"
                            value="{{ old('scores.'.$mp->participant_id, $mp->score ?? 0) }}"
                            min="0"
                            class="form-input"
                            required
                        >
                    </div>
                @endforeach

                <div>
                    <label class="form-label">Pemenang (opsional)</label>
                    <select name="winner_id" class="form-select">
                        <option value="">Otomatis (skor tertinggi)</option>
                        @foreach ($match->matchParticipants as $mp)
                            <option value="{{ $mp->participant_id }}" @selected(old('winner_id') == $mp->participant_id)>{{ $mp->participant->name }}</option>
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
