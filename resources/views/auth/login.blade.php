<x-guest-layout>
    <h2 class="text-2xl font-bold text-secondary dark:text-white">Masuk</h2>
    <p class="mt-1 text-sm text-slate-500">Masuk ke dashboard kompetisi 17an</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-input" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password" class="form-input" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary" name="remember">
                <span class="text-sm text-slate-600 dark:text-slate-400">Ingat saya</span>
            </label>
        </div>

        <x-ui.button type="submit" class="w-full">Masuk</x-ui.button>

        @if (Route::has('password.request'))
            <p class="text-center text-sm">
                <a href="{{ route('password.request') }}" class="text-primary hover:underline">Lupa password?</a>
            </p>
        @endif
    </form>
</x-guest-layout>
