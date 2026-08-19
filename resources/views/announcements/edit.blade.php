<x-app-layout title="Edit Pengumuman">
    <x-ui.page-header title="Edit Pengumuman" :description="$announcement->title">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('announcements.index')">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="form-label">Judul <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $announcement->title) }}" class="form-input" required>
                @error('title')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="content" class="form-label">Isi Pengumuman <span class="text-red-500">*</span></label>
                <textarea id="content" name="content" class="form-textarea min-h-[200px]" required>{{ old('content', $announcement->content) }}</textarea>
                @error('content')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="published_at" class="form-label">Tanggal Publish</label>
                    <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', $announcement->published_at?->format('Y-m-d\TH:i')) }}" class="form-input">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-primary focus:ring-primary" @checked(old('is_published', $announcement->is_published))>
                        <span class="text-sm font-medium">Published</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                <x-ui.button variant="outline" :href="route('announcements.index')">Batal</x-ui.button>
                <x-ui.button type="submit">Perbarui</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
