<x-guest-layout>
    <h2 class="text-2xl font-bold text-secondary dark:text-white">Reset Password</h2>
    <p class="mt-1 text-sm text-slate-500">Masukkan password baru Anda</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-input" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="form-label">Password Baru</label>
            <input id="password" type="password" name="password" class="form-input" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <x-ui.button type="submit" class="w-full">Reset Password</x-ui.button>
    </form>
</x-guest-layout>
