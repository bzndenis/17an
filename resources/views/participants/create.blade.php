@php
    use App\Enums\ParticipantStatus;
@endphp

<x-app-layout title="Tambah Peserta">
    <x-ui.page-header title="Tambah Peserta" description="Daftarkan peserta baru ke event aktif">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('participants.index')">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('participants.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-input" required>
                    @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="number" class="form-label">Nomor Peserta <span class="text-red-500">*</span></label>
                    <input type="number" id="number" name="number" value="{{ old('number') }}" class="form-input" min="1" required>
                    @error('number')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="category_id" class="form-label">Kategori</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="gender" class="form-label">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select id="gender" name="gender" class="form-select" required>
                        <option value="">Pilih</option>
                        <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                        <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                    </select>
                    @error('gender')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="dob" class="form-label">Tanggal Lahir</label>
                    <input type="date" id="dob" name="dob" value="{{ old('dob') }}" class="form-input">
                    @error('dob')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone" class="form-label">Telepon</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-input">
                    @error('phone')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="team" class="form-label">Tim / RT-RW</label>
                    <input type="text" id="team" name="team" value="{{ old('team') }}" class="form-input">
                    @error('team')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="rt_rw" class="form-label">RT/RW</label>
                    <input type="text" id="rt_rw" name="rt_rw" value="{{ old('rt_rw') }}" class="form-input">
                    @error('rt_rw')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        @foreach (ParticipantStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', 'active') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="photo" class="form-label">Foto</label>
                    <input type="file" id="photo" name="photo" accept="image/*" class="form-input file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-3 file:py-1.5 file:text-sm file:text-white">
                    @error('photo')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea id="address" name="address" class="form-textarea">{{ old('address') }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                <x-ui.button variant="outline" :href="route('participants.index')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan Peserta</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
