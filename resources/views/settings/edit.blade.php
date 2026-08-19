<x-app-layout title="Pengaturan">
    <x-ui.page-header title="Pengaturan Event" description="Konfigurasi tampilan dan preferensi event aktif" />

    @if (!$event)
        <x-ui.empty-state title="Tidak Ada Event Aktif" description="Pilih event terlebih dahulu untuk mengatur pengaturan." icon="settings" />
    @else
        <x-ui.card class="max-w-2xl">
            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-900/50">
                    <p class="text-sm font-medium text-secondary dark:text-white">Event Aktif</p>
                    <p class="text-lg font-bold text-primary">{{ $event->name }}</p>
                </div>

                <div>
                    <label for="logo" class="form-label">Logo Event</label>
                    @if ($settings->logo ?? null)
                        <img src="{{ Storage::url($settings->logo) }}" alt="Logo" class="mb-3 h-16 object-contain">
                    @endif
                    <input type="file" id="logo" name="logo" accept="image/*" class="form-input file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-3 file:py-1.5 file:text-sm file:text-white">
                </div>

                <div>
                    <label for="theme_color" class="form-label">Warna Tema <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="theme_color" name="theme_color" value="{{ old('theme_color', $settings->theme_color ?? '#D71920') }}" class="h-10 w-14 cursor-pointer rounded border border-slate-200">
                        <input type="text" value="{{ old('theme_color', $settings->theme_color ?? '#D71920') }}" class="form-input flex-1" readonly>
                    </div>
                    @error('theme_color')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="venue_default" class="form-label">Venue Default</label>
                    <input type="text" id="venue_default" name="venue_default" value="{{ old('venue_default', $settings->venue_default ?? '') }}" class="form-input" placeholder="Contoh: Lapangan Utama">
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                    <x-ui.button variant="outline" :href="route('events.index')">Kelola Event</x-ui.button>
                    <x-ui.button type="submit">Simpan Pengaturan</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif
</x-app-layout>
