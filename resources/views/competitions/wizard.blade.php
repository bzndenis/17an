@php
    use App\Enums\CompetitionStatus;
    $selectedIds = old('participant_ids', $competition->participants->pluck('id')->toArray());
    $seeds = old('seeds', $competition->participants->pluck('pivot.seed', 'id')->toArray());
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
                toggle(id) {
                    id = String(id);
                    if (this.selected.includes(id)) {
                        this.selected = this.selected.filter(i => i !== id);
                    } else {
                        this.selected.push(id);
                    }
                },
                isSelected(id) { return this.selected.includes(String(id)); }
            }">
                @csrf
                <input type="hidden" name="step" value="2">

                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-slate-500">Pilih minimal 2 peserta untuk lomba ini.</p>
                    <span class="text-sm font-medium text-primary" x-text="selected.length + ' dipilih'"></span>
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
                                <div>
                                    <label class="text-xs text-slate-500">Seed</label>
                                    <input
                                        type="number"
                                        name="seeds[{{ $participant->id }}]"
                                        value="{{ $seeds[$participant->id] ?? $loop->iteration }}"
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
                                <p class="text-sm text-slate-500">Buat bracket knockout berdasarkan peserta yang sudah dipilih.</p>
                            </div>
                        </label>
                    </div>

                    <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-900/50">
                        <p class="text-sm font-medium text-secondary dark:text-white">Ringkasan</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $competition->participants->count() }} peserta terdaftar · Sistem: {{ $competition->system->label() }}</p>
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
