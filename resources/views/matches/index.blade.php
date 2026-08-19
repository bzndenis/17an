@php
    use App\Enums\MatchStatus;
@endphp

<x-app-layout title="Pertandingan">
    <x-ui.page-header title="Pertandingan" description="Kelola semua pertandingan event aktif">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('competitions.index')">Lihat Lomba</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="mb-6">
        <form method="GET" class="grid gap-4 sm:grid-cols-4">
            <div>
                <label class="form-label">Cari</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Lomba, venue..." class="form-input">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    @foreach (MatchStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Lomba</label>
                <select name="competition_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($competitions as $comp)
                        <option value="{{ $comp->id }}" @selected(($filters['competition_id'] ?? '') == $comp->id)>{{ $comp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <x-ui.button type="submit" class="flex-1">Filter</x-ui.button>
                <x-ui.button variant="outline" :href="route('matches.index')">Reset</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($matches->isEmpty())
        <x-ui.empty-state title="Belum Ada Pertandingan" description="Buat lomba dan generate bracket untuk membuat pertandingan." icon="swords" :actionHref="route('competitions.index')" actionLabel="Ke Lomba" />
    @else
        <div class="space-y-3">
            @foreach ($matches as $match)
                <x-ui.card class="!p-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-primary">{{ $match->competition->name ?? '-' }}</span>
                                <span class="text-xs text-slate-400">· {{ $match->round->name ?? 'Babak' }} #{{ $match->match_number }}</span>
                                <x-ui.badge :variant="$match->status->value === 'live' ? 'live' : ($match->status->value === 'finished' ? 'success' : 'default')" size="sm">
                                    {{ $match->status->label() }}
                                </x-ui.badge>
                            </div>
                            <div class="mt-3 flex items-center gap-4">
                                @foreach ($match->matchParticipants as $mp)
                                    <div class="flex items-center gap-2 {{ $mp->is_winner ? 'font-bold text-primary' : '' }}">
                                        <span>{{ $mp->participant->name ?? 'TBD' }}</span>
                                        @if ($match->status->value === 'finished' || $mp->score !== null)
                                            <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-sm dark:bg-slate-700">{{ $mp->score ?? 0 }}</span>
                                        @endif
                                    </div>
                                    @if (!$loop->last)
                                        <span class="text-slate-400 font-medium">VS</span>
                                    @endif
                                @endforeach
                            </div>
                            @if ($match->scheduled_at)
                                <p class="mt-2 text-xs text-slate-500">
                                    <i data-lucide="clock" class="inline h-3 w-3"></i>
                                    {{ $match->scheduled_at->format('d M Y, H:i') }}
                                    @if ($match->venue) · {{ $match->venue }} @endif
                                </p>
                            @endif
                        </div>
                        <x-ui.button variant="outline" size="sm" :href="route('matches.show', $match)">Detail</x-ui.button>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
        <div class="mt-4">{{ $matches->links() }}</div>
    @endif
</x-app-layout>
