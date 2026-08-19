@php use App\Enums\EventStatus; @endphp

<x-app-layout title="Event">
    <x-ui.page-header title="Manajemen Event" description="Buat, edit, dan kelola event/perayaan">
        <x-slot:actions>
            <x-ui.button :href="route('events.create')">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Tambah Event
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($events->isEmpty())
        <x-ui.empty-state
            title="Belum Ada Event"
            description="Buat event pertama untuk mulai mengelola lomba."
            icon="calendar-days"
            :actionHref="route('events.create')"
            actionLabel="Tambah Event"
        />
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($events as $event)
                <x-ui.card class="relative">
                    @if ($activeEventId === $event->id)
                        <span class="absolute right-4 top-4 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                            Sedang Aktif
                        </span>
                    @endif

                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-secondary dark:text-white">{{ $event->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $event->year }} · {{ $event->start_date->format('d M') }} – {{ $event->end_date->format('d M Y') }}</p>
                    </div>

                    <div class="mb-4 flex flex-wrap gap-2">
                        <x-ui.badge :variant="match($event->status) {
                            EventStatus::Active => 'success',
                            EventStatus::Completed => 'default',
                            EventStatus::Cancelled => 'danger',
                            default => 'default',
                        }">{{ $event->status->label() }}</x-ui.badge>
                        @if ($event->is_active)
                            <x-ui.badge variant="live">Default Aktif</x-ui.badge>
                        @endif
                    </div>

                    <div class="mb-4 grid grid-cols-2 gap-3 text-center">
                        <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-900/50">
                            <p class="text-lg font-bold text-secondary dark:text-white">{{ $event->participants_count }}</p>
                            <p class="text-xs text-slate-500">Peserta</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-2 dark:bg-slate-900/50">
                            <p class="text-lg font-bold text-secondary dark:text-white">{{ $event->competitions_count }}</p>
                            <p class="text-xs text-slate-500">Lomba</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-4 dark:border-slate-700">
                        @if ($activeEventId !== $event->id)
                            <form action="{{ route('events.switch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->id }}">
                                <x-ui.button variant="outline" size="sm" type="submit">Aktifkan</x-ui.button>
                            </form>
                        @endif
                        <x-ui.button variant="outline" size="sm" :href="route('events.edit', $event)">Edit</x-ui.button>
                        @if ($event->participants_count === 0 && $event->competitions_count === 0)
                            <x-ui.confirm-delete :action="route('events.destroy', $event)">
                                <x-ui.button variant="ghost" size="sm" type="button">Hapus</x-ui.button>
                            </x-ui.confirm-delete>
                        @endif
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div class="mt-6">{{ $events->links() }}</div>
    @endif
</x-app-layout>
