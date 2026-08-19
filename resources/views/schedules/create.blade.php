<x-app-layout title="Tambah Jadwal">
    <x-ui.page-header title="Tambah Jadwal" description="Buat jadwal kegiatan baru">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('schedules.index')">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="max-w-2xl">
        <form method="POST" action="{{ route('schedules.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="title" class="form-label">Judul <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-input" required>
                @error('title')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="form-label">Deskripsi</label>
                <textarea id="description" name="description" class="form-textarea">{{ old('description') }}</textarea>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="datetime" class="form-label">Tanggal & Waktu <span class="text-red-500">*</span></label>
                    <input type="datetime-local" id="datetime" name="datetime" value="{{ old('datetime') }}" class="form-input" required>
                    @error('datetime')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="location" class="form-label">Lokasi</label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}" class="form-input">
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                <x-ui.button variant="outline" :href="route('schedules.index')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
