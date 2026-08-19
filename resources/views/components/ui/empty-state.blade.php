@props([
    'title',
    'description' => null,
    'icon' => 'inbox',
    'action' => null,
    'actionLabel' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center dark:border-slate-600 dark:bg-slate-800']) }}>
    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700">
        <i data-lucide="{{ $icon }}" class="h-7 w-7 text-slate-400"></i>
    </div>
    <h3 class="text-lg font-semibold text-secondary dark:text-white">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 max-w-sm text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endif
    @if ($actionHref)
        <x-ui.button :href="$actionHref" class="mt-6">
            {{ $actionLabel ?? 'Mulai' }}
        </x-ui.button>
    @elseif ($action)
        <div class="mt-6">{{ $action }}</div>
    @endif
</div>
