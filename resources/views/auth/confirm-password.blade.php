<x-guest-layout>
    <h2 class="text-2xl font-bold text-secondary dark:text-white">Konfirmasi Password</h2>
    <p class="mt-1 text-sm text-slate-500">Area aman. Konfirmasi password Anda untuk melanjutkan.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password" class="form-input" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <x-ui.button type="submit" class="w-full">Konfirmasi</x-ui.button>
    </form>
</x-guest-layout>
