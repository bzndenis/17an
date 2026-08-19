@php use App\Enums\EventStatus; @endphp

<x-app-layout :title="$event ? 'Edit Event' : 'Tambah Event'">
    <x-ui.page-header
        :title="$event ? 'Edit Event' : 'Tambah Event'"
        :description="$event ? 'Perbarui informasi event' : 'Buat event/perayaan baru'"
    >
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('events.index')">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="max-w-2xl">
        <form
            method="POST"
            action="{{ $event ? route('events.update', $event) : route('events.store') }}"
            class="space-y-5"
        >
            @csrf
            @if ($event) @method('PUT') @endif

            <div>
                <label for="name" class="form-label">Nama Event <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $event->name ?? '') }}" class="form-input" placeholder="Festival 17 Agustus 2026" required>
                @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="year" class="form-label">Tahun <span class="text-red-500">*</span></label>
                    <input type="number" id="year" name="year" value="{{ old('year', $event->year ?? date('Y')) }}" class="form-input" min="2000" max="2100" required>
                    @error('year')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                    <select id="status" name="status" class="form-select" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $event?->status?->value ?? 'draft') === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="start_date" class="form-label">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', isset($event) ? $event->start_date->format('Y-m-d') : '') }}" class="form-input" required>
                    @error('start_date')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="end_date" class="form-label">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', isset($event) ? $event->end_date->format('Y-m-d') : '') }}" class="form-input" required>
                    @error('end_date')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="venue_default" class="form-label">Venue Default</label>
                    <input type="text" id="venue_default" name="venue_default" value="{{ old('venue_default', $event?->settings?->venue_default ?? '') }}" class="form-input" placeholder="Lapangan Utama">
                </div>
                <div>
                    <label for="theme_color" class="form-label">Warna Tema</label>
                    <input type="color" id="theme_color" name="theme_color" value="{{ old('theme_color', $event?->settings?->theme_color ?? '#D71920') }}" class="h-10 w-full cursor-pointer rounded border border-slate-200">
                </div>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event?->is_active ?? false)) class="rounded border-slate-300 text-primary focus:ring-primary">
                <span class="text-sm text-secondary dark:text-slate-300">Jadikan event default aktif</span>
            </label>

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                <x-ui.button type="submit">{{ $event ? 'Simpan Perubahan' : 'Buat Event' }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
