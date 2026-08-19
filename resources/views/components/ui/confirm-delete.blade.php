@props([
    'action',
    'title' => 'Konfirmasi Hapus',
    'message' => 'Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.',
    'confirmLabel' => 'Ya, Hapus',
])

<div x-data="{ open: false }" {{ $attributes }}>
    <div @click="open = true">
        {{ $slot }}
    </div>

    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-800">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-secondary dark:text-white">{{ $title }}</h3>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $message }}</p>
            <div class="mt-6 flex justify-end gap-3">
                <x-ui.button variant="outline" type="button" @click="open = false">Batal</x-ui.button>
                <form action="{{ $action }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <x-ui.button variant="danger" type="submit">{{ $confirmLabel }}</x-ui.button>
                </form>
            </div>
        </div>
    </div>
</div>
