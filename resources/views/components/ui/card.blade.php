@props([
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800' . ($padding ? ' p-5 sm:p-6' : '')]) }}>
    {{ $slot }}
</div>
