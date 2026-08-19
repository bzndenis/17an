@php
    use App\Enums\ParticipantStatus;
@endphp

<x-app-layout title="Peserta">
    <x-ui.page-header title="Peserta" description="Kelola data peserta kompetisi">
        <x-slot:actions>
            <form method="POST" action="{{ route('participants.import') }}" enctype="multipart/form-data" class="inline-flex" x-data="{ uploading: false }" @submit="uploading = true">
                @csrf
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    <i data-lucide="upload" class="h-4 w-4"></i>
                    Import Excel
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden" @change="$el.form.submit()">
                </label>
            </form>
            <x-ui.button variant="outline" :href="route('participants.export')">
                <i data-lucide="download" class="h-4 w-4"></i>
                Export Excel
            </x-ui.button>
            <x-ui.button :href="route('participants.create')">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Tambah Peserta
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Filters --}}
    <x-ui.card class="mb-6">
        <form method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="form-label">Cari</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, nomor, tim..." class="form-input">
            </div>
            <div>
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">Semua</option>
                    <option value="male" @selected(($filters['gender'] ?? '') === 'male')>Laki-laki</option>
                    <option value="female" @selected(($filters['gender'] ?? '') === 'female')>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    @foreach (ParticipantStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <x-ui.button type="submit" class="flex-1">Filter</x-ui.button>
                <x-ui.button variant="outline" :href="route('participants.index')">Reset</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($participants->isEmpty())
        <x-ui.empty-state
            title="Belum Ada Peserta"
            description="Tambahkan peserta pertama untuk event ini."
            icon="users"
            :actionHref="route('participants.create')"
            actionLabel="Tambah Peserta"
        />
    @else
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Gender</th>
                        <th>Tim</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($participants as $participant)
                        <tr>
                            <td class="font-mono font-medium">{{ $participant->number }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($participant->photo)
                                        <img src="{{ Storage::url($participant->photo) }}" alt="" class="h-8 w-8 rounded-full object-cover">
                                    @else
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">
                                            {{ strtoupper(substr($participant->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <a href="{{ route('participants.show', $participant) }}" class="font-medium hover:text-primary">{{ $participant->name }}</a>
                                </div>
                            </td>
                            <td>{{ $participant->category->name ?? '-' }}</td>
                            <td>{{ $participant->gender === 'male' ? 'L' : 'P' }}</td>
                            <td>{{ $participant->team ?? '-' }}</td>
                            <td>
                                <x-ui.badge :variant="match($participant->status->value) {
                                    'active' => 'success',
                                    'eliminated' => 'danger',
                                    'withdrawn' => 'warning',
                                    default => 'default',
                                }">{{ $participant->status->label() }}</x-ui.badge>
                            </td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <x-ui.button variant="ghost" size="sm" :href="route('participants.edit', $participant)">
                                        <i data-lucide="pencil" class="h-4 w-4"></i>
                                    </x-ui.button>
                                    <x-ui.confirm-delete :action="route('participants.destroy', $participant)">
                                        <x-ui.button variant="ghost" size="sm" type="button">
                                            <i data-lucide="trash-2" class="h-4 w-4 text-red-500"></i>
                                        </x-ui.button>
                                    </x-ui.confirm-delete>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $participants->links() }}</div>
    @endif
</x-app-layout>
