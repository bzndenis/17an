@php
    use App\Enums\CompetitionStatus;
    use App\Enums\CompetitionSystem;
    $selectedIds = old('participant_ids', $competition->participants->pluck('id')->toArray());
    $seeds = old('seeds', $competition->participants->pluck('pivot.seed', 'id')->toArray());
    $isGroupKnockout = $competition->system === CompetitionSystem::GroupKnockout;
    $groupCount = old('group_count', $competition->config['group_count'] ?? 4);
    $qualifyPerGroup = old('qualify_per_group', $competition->config['qualify_per_group'] ?? 2);
@endphp

<x-app-layout :title="'Wizard - ' . $competition->name">
    <x-ui.page-header :title="'Wizard Lomba'" :description="$competition->name">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('competitions.show', $competition)">Keluar Wizard</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Step indicator --}}
    <div class="mb-8" x-data="{ step: {{ $step }} }">
        <div class="flex items-center justify-center">
            @foreach ([1 => 'Info Lomba', 2 => 'Pilih Peserta', 3 => 'Finalisasi'] as $num => $label)
                <div class="flex items-center">
                    <div class="flex flex-col items-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold {{ $step >= $num ? 'bg-primary text-white' : 'bg-slate-200 text-slate-500 dark:bg-slate-700' }}">
                            {{ $num }}
                        </div>
                        <span class="mt-2 hidden text-xs font-medium sm:block {{ $step >= $num ? 'text-primary' : 'text-slate-400' }}">{{ $label }}</span>
                    </div>
                    @if ($num < 3)
                        <div class="mx-4 h-0.5 w-16 sm:w-24 {{ $step > $num ? 'bg-primary' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @if ($step === 1)
        <x-ui.card class="max-w-3xl mx-auto">
            <p class="text-sm text-slate-500 mb-4">Informasi lomba sudah dibuat. Lanjutkan ke langkah berikutnya untuk mendaftarkan peserta.</p>
            <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">Nama</dt><dd class="font-medium">{{ $competition->name }}</dd></div>
                <div><dt class="text-slate-500">Sistem</dt><dd class="font-medium">{{ $competition->system->label() }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd class="font-medium">{{ $competition->status->label() }}</dd></div>
                <div><dt class="text-slate-500">Kategori</dt><dd class="font-medium">{{ $competition->category ?? '-' }}</dd></div>
            </dl>
            <div class="mt-6 flex justify-end">
                <x-ui.button :href="route('competitions.wizard', ['competition' => $competition, 'step' => 2])">Lanjut ke Peserta</x-ui.button>
            </div>
        </x-ui.card>

    @elseif ($step === 2)
        <x-ui.card class="max-w-4xl mx-auto">
            <form method="POST" action="{{ route('competitions.wizard.save', $competition) }}" x-data="{
                selected: @js(array_map('strval', $selectedIds)),
                seeds: @js(collect($participants)->mapWithKeys(fn ($p, $i) => [(string) $p->id => (int) ($seeds[$p->id] ?? ($i + 1))])->all()),
                groupCount: {{ (int) $groupCount }},
                toggle(id) {
                    id = String(id);
                    if (this.selected.includes(id)) {
                        this.selected = this.selected.filter(i => i !== id);
                    } else {
                        this.selected.push(id);
                    }
                },
                isSelected(id) { return this.selected.includes(String(id)); },
                selectAll() {
                    this.selected = @js($participants->pluck('id')->map(fn ($id) => (string) $id)->values()->all());
                },
                clearAll() { this.selected = []; },
                randomizeSeeds() {
                    const ids = this.selected.length
                        ? [...this.selected]
                        : Object.keys(this.seeds);
                    const order = ids.map((_, i) => i + 1);
                    for (let i = order.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [order[i], order[j]] = [order[j], order[i]];
                    }
                    ids.forEach((id, index) => {
                        this.seeds[String(id)] = order[index];
                    });
                },
                perGroup() {
                    if (!this.groupCount || this.groupCount < 1) return 0;
                    return Math.floor(this.selected.length / this.groupCount);
                }
            }">
                @csrf
                <input type="hidden" name="step" value="2">

                @if ($isGroupKnockout)
                    <div class="mb-6 rounded-xl border border-primary/20 bg-primary/5 p-4">
                        <p class="mb-3 text-sm font-semibold text-secondary dark:text-white">Pengaturan Grup + Eliminasi</p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="group_count" class="form-label">Jumlah Grup</label>
                                <input
                                    type="number"
                                    id="group_count"
                                    name="group_count"
                                    x-model.number="groupCount"
                                    value="{{ $groupCount }}"
                                    min="2"
                                    max="16"
                                    class="form-input"
                                    required
                                >
                                @error('group_count')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="qualify_per_group" class="form-label">Lolos per Grup</label>
                                <input
                                    type="number"
                                    id="qualify_per_group"
                                    name="qualify_per_group"
                                    value="{{ $qualifyPerGroup }}"
                                    min="1"
                                    max="4"
                                    class="form-input"
                                >
                                <p class="mt-1 text-xs text-slate-500">Peserta terbaik tiap grup masuk fase knockout.</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-slate-500">
                            <span x-text="selected.length"></span> peserta ·
                            ~<span x-text="perGroup()"></span> orang/grup
                            (minimal 2 orang per grup)
                        </p>
                    </div>
                @endif

                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-slate-500">Pilih minimal 2 peserta untuk lomba ini.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-medium text-primary" x-text="selected.length + ' dipilih'"></span>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-secondary hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                            @click="selectAll()"
                        >
                            Pilih semua
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-secondary hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                            @click="randomizeSeeds()"
                            title="Acak nomor seed peserta yang dipilih"
                        >
                            <i data-lucide="shuffle" class="h-3.5 w-3.5"></i>
                            Acak Seed
                        </button>
                    </div>
                </div>

                @if ($participants->isEmpty())
                    <x-ui.empty-state title="Belum Ada Peserta" description="Tambahkan peserta terlebih dahulu." icon="users" :actionHref="route('participants.create')" actionLabel="Tambah Peserta" />
                @else
                    <div class="max-h-96 overflow-y-auto space-y-2">
                        @foreach ($participants as $participant)
                            <label
                                class="flex cursor-pointer items-center gap-4 rounded-lg border p-3 transition"
                                :class="isSelected({{ $participant->id }}) ? 'border-primary bg-primary/5' : 'border-slate-200 dark:border-slate-700'"
                            >
                                <input
                                    type="checkbox"
                                    name="participant_ids[]"
                                    value="{{ $participant->id }}"
                                    class="rounded border-slate-300 text-primary focus:ring-primary"
                                    :checked="isSelected({{ $participant->id }})"
                                    @change="toggle({{ $participant->id }})"
                                >
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xs font-bold dark:bg-slate-700">
                                    {{ $participant->number }}
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium">{{ $participant->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $participant->category->name ?? '-' }} · {{ $participant->team ?? '-' }}</p>
                                </div>
                                <div @click.stop>
                                    <label class="text-xs text-slate-500">Seed</label>
                                    <input
                                        type="number"
                                        name="seeds[{{ $participant->id }}]"
                                        x-model.number="seeds['{{ $participant->id }}']"
                                        min="1"
                                        class="form-input w-16 py-1 text-center"
                                    >
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('participant_ids')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror

                    <div class="mt-6 flex justify-between">
                        <x-ui.button variant="outline" :href="route('competitions.wizard', ['competition' => $competition, 'step' => 1])">Kembali</x-ui.button>
                        <x-ui.button type="submit">Simpan & Lanjut</x-ui.button>
                    </div>
                @endif
            </form>
        </x-ui.card>

    @elseif ($step === 3)
        <x-ui.card class="max-w-2xl mx-auto">
            <form method="POST" action="{{ route('competitions.wizard.save', $competition) }}">
                @csrf
                <input type="hidden" name="step" value="3">

                <div class="space-y-5">
                    <div>
                        <label for="status" class="form-label">Status Lomba</label>
                        <select id="status" name="status" class="form-select">
                            @foreach (CompetitionStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $competition->status->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="generate_bracket" value="1" class="mt-1 rounded border-slate-300 text-primary focus:ring-primary" @checked(old('generate_bracket'))>
                            <div>
                                <p class="font-medium text-secondary dark:text-white">Generate Bracket Otomatis</p>
                                <p class="text-sm text-slate-500">
                                    @if ($isGroupKnockout)
                                        Buat fase grup + bracket knockout berdasarkan pengaturan grup.
                                    @else
                                        Buat bracket berdasarkan peserta yang sudah dipilih.
                                    @endif
                                </p>
                            </div>
                        </label>
                    </div>

                    <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-900/50">
                        <p class="text-sm font-medium text-secondary dark:text-white">Ringkasan</p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $competition->participants->count() }} peserta terdaftar · Sistem: {{ $competition->system->label() }}
                            @if ($isGroupKnockout)
                                · {{ $competition->config['group_count'] ?? 4 }} grup · {{ $competition->config['qualify_per_group'] ?? 2 }} lolos/grup
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-between">
                    <x-ui.button variant="outline" :href="route('competitions.wizard', ['competition' => $competition, 'step' => 2])">Kembali</x-ui.button>
                    <x-ui.button type="submit">Selesai Wizard</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif
</x-app-layout>
