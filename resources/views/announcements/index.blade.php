<x-app-layout title="Pengumuman">
    <x-ui.page-header title="Pengumuman" description="Kelola pengumuman event aktif">
        <x-slot:actions>
            <x-ui.button :href="route('announcements.create')">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Buat Pengumuman
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($announcements->isEmpty())
        <x-ui.empty-state title="Belum Ada Pengumuman" description="Buat pengumuman untuk peserta dan panitia." icon="megaphone" :actionHref="route('announcements.create')" actionLabel="Buat Pengumuman" />
    @else
        <div class="space-y-3">
            @foreach ($announcements as $announcement)
                <x-ui.card class="!p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-secondary dark:text-white">{{ $announcement->title }}</h3>
                                <x-ui.badge :variant="$announcement->is_published ? 'success' : 'default'" size="sm">
                                    {{ $announcement->is_published ? 'Published' : 'Draft' }}
                                </x-ui.badge>
                            </div>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 line-clamp-2">{{ Str::limit(strip_tags($announcement->content), 150) }}</p>
                            <p class="mt-2 text-xs text-slate-500">
                                {{ $announcement->published_at?->format('d M Y, H:i') ?? $announcement->created_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <x-ui.button variant="outline" size="sm" :href="route('announcements.edit', $announcement)">Edit</x-ui.button>
                            <x-ui.confirm-delete :action="route('announcements.destroy', $announcement)">
                                <x-ui.button variant="ghost" size="sm" type="button">
                                    <i data-lucide="trash-2" class="h-4 w-4 text-red-500"></i>
                                </x-ui.button>
                            </x-ui.confirm-delete>
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
        <div class="mt-4">{{ $announcements->links() }}</div>
    @endif
</x-app-layout>
