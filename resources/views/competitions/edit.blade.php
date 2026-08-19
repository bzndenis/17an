@php
    use App\Enums\CompetitionStatus;
    use App\Enums\CompetitionSystem;
@endphp

<x-app-layout :title="'Edit ' . $competition->name">
    <x-ui.page-header :title="'Edit ' . $competition->name" description="Perbarui informasi lomba">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('competitions.show', $competition)">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('competitions.update', $competition) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="form-label">Nama Lomba <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $competition->name) }}" class="form-input" required>
                    @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea id="description" name="description" class="form-textarea">{{ old('description', $competition->description) }}</textarea>
                </div>

                <div>
                    <label for="category" class="form-label">Kategori</label>
                    <input type="text" id="category" name="category" value="{{ old('category', $competition->category) }}" class="form-input">
                </div>

                <div>
                    <label for="system" class="form-label">Sistem <span class="text-red-500">*</span></label>
                    <select id="system" name="system" class="form-select" required>
                        @foreach (CompetitionSystem::cases() as $system)
                            <option value="{{ $system->value }}" @selected(old('system', $competition->system->value) === $system->value)>{{ $system->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        @foreach (CompetitionStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $competition->status->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="location" class="form-label">Lokasi</label>
                    <input type="text" id="location" name="location" value="{{ old('location', $competition->location) }}" class="form-input">
                </div>

                <div>
                    <label for="start_at" class="form-label">Waktu Mulai</label>
                    <input type="datetime-local" id="start_at" name="start_at" value="{{ old('start_at', $competition->start_at?->format('Y-m-d\TH:i')) }}" class="form-input">
                </div>

                <div>
                    <label for="duration_minutes" class="form-label">Durasi (menit)</label>
                    <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $competition->duration_minutes) }}" class="form-input" min="1">
                </div>

                <div>
                    <label for="prize_1" class="form-label">Hadiah Juara 1</label>
                    <input type="text" id="prize_1" name="prize_1" value="{{ old('prize_1', $competition->prize_1) }}" class="form-input">
                </div>

                <div>
                    <label for="prize_2" class="form-label">Hadiah Juara 2</label>
                    <input type="text" id="prize_2" name="prize_2" value="{{ old('prize_2', $competition->prize_2) }}" class="form-input">
                </div>

                <div>
                    <label for="prize_3" class="form-label">Hadiah Juara 3</label>
                    <input type="text" id="prize_3" name="prize_3" value="{{ old('prize_3', $competition->prize_3) }}" class="form-input">
                </div>

                <div class="sm:col-span-2">
                    <label for="banner" class="form-label">Banner</label>
                    @if ($competition->banner)
                        <img src="{{ Storage::url($competition->banner) }}" alt="" class="mb-2 h-24 rounded-lg object-cover">
                    @endif
                    <input type="file" id="banner" name="banner" accept="image/*" class="form-input file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-3 file:py-1.5 file:text-sm file:text-white">
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                <x-ui.button variant="outline" :href="route('competitions.show', $competition)">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
