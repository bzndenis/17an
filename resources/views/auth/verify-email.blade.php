<x-guest-layout>
    <h2 class="text-2xl font-bold text-secondary dark:text-white">Verifikasi Email</h2>
    <p class="mt-1 text-sm text-slate-500">
        Terima kasih sudah mendaftar! Silakan verifikasi email Anda dengan mengklik link yang kami kirimkan.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
            Link verifikasi baru telah dikirim ke email Anda.
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit">Kirim Ulang Email Verifikasi</x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-slate-500 hover:text-primary">Keluar</button>
        </form>
    </div>
</x-guest-layout>
