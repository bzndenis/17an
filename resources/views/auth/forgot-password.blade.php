<x-guest-layout>
    <h2 class="text-2xl font-bold text-secondary dark:text-white">Lupa Password</h2>
    <p class="mt-1 text-sm text-slate-500">Masukkan email untuk menerima link reset password</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-input" required autofocus>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <x-ui.button type="submit" class="w-full">Kirim Link Reset</x-ui.button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="text-primary hover:underline">Kembali ke login</a>
        </p>
    </form>
</x-guest-layout>
