@props([
    'variant' => 'default',
    'size' => 'md',
])

@php
    $variants = [
        'default' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
        'primary' => 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-red-400',
        'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'danger' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'info' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'live' => 'bg-red-100 text-red-700 animate-pulse dark:bg-red-900/30 dark:text-red-400',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1 text-sm',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full font-medium ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['md'])]) }}>
    {{ $slot }}
</span>
