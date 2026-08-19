@props([
    'label',
    'value',
    'icon' => null,
    'trend' => null,
    'color' => 'primary',
])

@php
    $iconColors = [
        'primary' => 'bg-primary/10 text-primary',
        'blue' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        'green' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
        'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        'purple' => 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
        'red' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
    ];
@endphp

<x-ui.card {{ $attributes->merge(['class' => 'relative overflow-hidden']) }}>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold text-secondary dark:text-white">{{ $value }}</p>
            @if ($trend)
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $trend }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $iconColors[$color] ?? $iconColors['primary'] }}">
                <i data-lucide="{{ $icon }}" class="h-5 w-5"></i>
            </div>
        @endif
    </div>
</x-ui.card>
