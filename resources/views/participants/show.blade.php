<x-app-layout :title="$participant->name">
    <x-ui.page-header :title="$participant->name" description="Detail peserta kompetisi">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('participants.index')">Kembali</x-ui.button>
            <x-ui.button :href="route('participants.edit', $participant)">
                <i data-lucide="pencil" class="h-4 w-4"></i>
                Edit
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            <div class="text-center">
                @if ($participant->photo)
                    <img src="{{ Storage::url($participant->photo) }}" alt="{{ $participant->name }}" class="mx-auto h-32 w-32 rounded-full object-cover ring-4 ring-primary/10">
                @else
                    <div class="mx-auto flex h-32 w-32 items-center justify-center rounded-full bg-primary/10 text-4xl font-bold text-primary">
                        {{ strtoupper(substr($participant->name, 0, 1)) }}
                    </div>
                @endif
                <h2 class="mt-4 text-xl font-bold text-secondary dark:text-white">{{ $participant->name }}</h2>
                <p class="text-sm text-slate-500">No. {{ $participant->number }}</p>
                <div class="mt-3">
                    <x-ui.badge :variant="match($participant->status->value) {
                        'active' => 'success',
                        'eliminated' => 'danger',
                        default => 'default',
                    }">{{ $participant->status->label() }}</x-ui.badge>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="lg:col-span-2">
            <h3 class="mb-4 text-lg font-semibold text-secondary dark:text-white">Informasi Peserta</h3>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Kategori</dt>
                    <dd class="mt-1 text-sm font-medium text-secondary dark:text-slate-200">{{ $participant->category->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Jenis Kelamin</dt>
                    <dd class="mt-1 text-sm font-medium text-secondary dark:text-slate-200">{{ $participant->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Tanggal Lahir</dt>
                    <dd class="mt-1 text-sm font-medium text-secondary dark:text-slate-200">{{ $participant->dob?->format('d M Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Telepon</dt>
                    <dd class="mt-1 text-sm font-medium text-secondary dark:text-slate-200">{{ $participant->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Tim</dt>
                    <dd class="mt-1 text-sm font-medium text-secondary dark:text-slate-200">{{ $participant->team ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">RT/RW</dt>
                    <dd class="mt-1 text-sm font-medium text-secondary dark:text-slate-200">{{ $participant->rt_rw ?? '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase text-slate-500">Alamat</dt>
                    <dd class="mt-1 text-sm text-secondary dark:text-slate-200">{{ $participant->address ?? '-' }}</dd>
                </div>
            </dl>
        </x-ui.card>

        @if ($participant->competitions->isNotEmpty())
            <x-ui.card class="lg:col-span-3">
                <h3 class="mb-4 text-lg font-semibold text-secondary dark:text-white">Lomba Diikuti</h3>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($participant->competitions as $competition)
                        <a href="{{ route('competitions.show', $competition) }}" class="rounded-lg border border-slate-200 p-4 transition hover:border-primary/30 dark:border-slate-700">
                            <p class="font-medium text-secondary dark:text-white">{{ $competition->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">Seed: {{ $competition->pivot->seed ?? '-' }}</p>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>
