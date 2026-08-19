@php
    use App\Enums\CompetitionStatus;
    use App\Enums\CompetitionSystem;
@endphp

<x-app-layout title="Lomba">
    <x-ui.page-header title="Lomba" description="Kelola semua lomba kompetisi">
        <x-slot:actions>
            <x-ui.button :href="route('competitions.create')">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Buat Lomba
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="mb-6">
        <form method="GET" class="grid gap-4 sm:grid-cols-4">
            <div>
                <label class="form-label">Cari</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama lomba..." class="form-input">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    @foreach (CompetitionStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Sistem</label>
                <select name="system" class="form-select">
                    <option value="">Semua</option>
                    @foreach (CompetitionSystem::cases() as $system)
                        <option value="{{ $system->value }}" @selected(($filters['system'] ?? '') === $system->value)>{{ $system->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <x-ui.button type="submit" class="flex-1">Filter</x-ui.button>
                <x-ui.button variant="outline" :href="route('competitions.index')">Reset</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($competitions->isEmpty())
        <x-ui.empty-state
            title="Belum Ada Lomba"
            description="Buat lomba pertama untuk memulai kompetisi."
            icon="trophy"
            :actionHref="route('competitions.create')"
            actionLabel="Buat Lomba"
        />
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($competitions as $competition)
                <x-ui.card class="group relative overflow-hidden !p-0">
                    @if ($competition->banner)
                        <img src="{{ Storage::url($competition->banner) }}" alt="" class="h-36 w-full object-cover">
                    @else
                        <div class="flex h-36 items-center justify-center bg-gradient-to-br from-primary/20 to-primary/5">
                            <i data-lucide="trophy" class="h-12 w-12 text-primary/40"></i>
                        </div>
                    @endif
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-2">
                            <a href="{{ route('competitions.show', $competition) }}" class="font-semibold text-secondary hover:text-primary dark:text-white">
                                {{ $competition->name }}
                            </a>
                            <x-ui.badge :variant="match($competition->status->value) {
                                'ongoing' => 'live',
                                'completed' => 'success',
                                default => 'default',
                            }" size="sm">{{ $competition->status->label() }}</x-ui.badge>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $competition->system->label() }} · {{ $competition->category ?? 'Umum' }}</p>
                        <div class="mt-3 flex items-center gap-4 text-xs text-slate-500">
                            <span><i data-lucide="users" class="inline h-3.5 w-3.5"></i> {{ $competition->participants_count }}</span>
                            <span><i data-lucide="swords" class="inline h-3.5 w-3.5"></i> {{ $competition->matches_count }}</span>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <x-ui.button variant="outline" size="sm" :href="route('competitions.show', $competition)" class="flex-1">Detail</x-ui.button>
                            <x-ui.button variant="ghost" size="sm" :href="route('competitions.edit', $competition)">
                                <i data-lucide="pencil" class="h-4 w-4"></i>
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
        <div class="mt-6">{{ $competitions->links() }}</div>
    @endif
</x-app-layout>
