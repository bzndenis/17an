<x-app-layout title="Jadwal">
    <x-ui.page-header title="Jadwal Kegiatan" description="Kelola jadwal event aktif">
        <x-slot:actions>
            <x-ui.button :href="route('schedules.create')">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Tambah Jadwal
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($schedules->isEmpty())
        <x-ui.empty-state title="Belum Ada Jadwal" description="Tambahkan jadwal kegiatan untuk event ini." icon="calendar" :actionHref="route('schedules.create')" actionLabel="Tambah Jadwal" />
    @else
        <div class="space-y-3">
            @foreach ($schedules as $schedule)
                <x-ui.card class="!p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex gap-4">
                            <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <span class="text-lg font-bold leading-none">{{ $schedule->datetime->format('d') }}</span>
                                <span class="text-xs uppercase">{{ $schedule->datetime->format('M') }}</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-secondary dark:text-white">{{ $schedule->title }}</h3>
                                @if ($schedule->description)
                                    <p class="mt-0.5 text-sm text-slate-500 line-clamp-1">{{ $schedule->description }}</p>
                                @endif
                                <div class="mt-1 flex flex-wrap gap-3 text-xs text-slate-500">
                                    <span><i data-lucide="clock" class="inline h-3 w-3"></i> {{ $schedule->datetime->format('H:i') }}</span>
                                    @if ($schedule->location)
                                        <span><i data-lucide="map-pin" class="inline h-3 w-3"></i> {{ $schedule->location }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <x-ui.button variant="outline" size="sm" :href="route('schedules.edit', $schedule)">Edit</x-ui.button>
                            <x-ui.confirm-delete :action="route('schedules.destroy', $schedule)">
                                <x-ui.button variant="ghost" size="sm" type="button">
                                    <i data-lucide="trash-2" class="h-4 w-4 text-red-500"></i>
                                </x-ui.button>
                            </x-ui.confirm-delete>
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
        <div class="mt-4">{{ $schedules->links() }}</div>
    @endif
</x-app-layout>
