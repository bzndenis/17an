@php
    use App\Enums\ParticipantStatus;
@endphp

<x-app-layout title="Edit Peserta">
    <x-ui.page-header title="Edit Peserta" :description="$participant->name">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('participants.index')">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="max-w-3xl">
        <form method="POST" action="{{ route('participants.update', $participant) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $participant->name) }}" class="form-input" required>
                    @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="number" class="form-label">Nomor Peserta <span class="text-red-500">*</span></label>
                    <input type="number" id="number" name="number" value="{{ old('number', $participant->number) }}" class="form-input" min="1" required>
                    @error('number')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="category_id" class="form-label">Kategori</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $participant->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="gender" class="form-label">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select id="gender" name="gender" class="form-select" required>
                        <option value="male" @selected(old('gender', $participant->gender) === 'male')>Laki-laki</option>
                        <option value="female" @selected(old('gender', $participant->gender) === 'female')>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label for="dob" class="form-label">Tanggal Lahir</label>
                    <input type="date" id="dob" name="dob" value="{{ old('dob', $participant->dob?->format('Y-m-d')) }}" class="form-input">
                </div>

                <div>
                    <label for="phone" class="form-label">Telepon</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $participant->phone) }}" class="form-input">
                </div>

                <div>
                    <label for="team" class="form-label">Tim</label>
                    <input type="text" id="team" name="team" value="{{ old('team', $participant->team) }}" class="form-input">
                </div>

                <div>
                    <label for="rt_rw" class="form-label">RT/RW</label>
                    <input type="text" id="rt_rw" name="rt_rw" value="{{ old('rt_rw', $participant->rt_rw) }}" class="form-input">
                </div>

                <div>
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        @foreach (ParticipantStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $participant->status->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="photo" class="form-label">Foto</label>
                    @if ($participant->photo)
                        <img src="{{ Storage::url($participant->photo) }}" alt="" class="mb-2 h-16 w-16 rounded-lg object-cover">
                    @endif
                    <input type="file" id="photo" name="photo" accept="image/*" class="form-input file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-3 file:py-1.5 file:text-sm file:text-white">
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea id="address" name="address" class="form-textarea">{{ old('address', $participant->address) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                <x-ui.button variant="outline" :href="route('participants.index')">Batal</x-ui.button>
                <x-ui.button type="submit">Perbarui</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
