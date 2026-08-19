<x-guest-layout>
    <h2 class="text-2xl font-bold text-secondary dark:text-white">Daftar</h2>
    <p class="mt-1 text-sm text-slate-500">Buat akun admin dashboard 17an</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="name" class="form-label">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-input" required autofocus autocomplete="name">
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-input" required autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password" class="form-input" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <x-ui.button type="submit" class="w-full">Daftar</x-ui.button>

        <p class="text-center text-sm text-slate-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-primary hover:underline">Masuk</a>
        </p>
    </form>
</x-guest-layout>
