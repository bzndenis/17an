<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' — ' : '' }}{{ config('app.name', '17an') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="h-full font-sans antialiased" x-data>
    <x-ui.toast />

    @if (session('success'))
        <div x-init="$nextTick(() => { Alpine.store('toast').add(@js(session('success')), 'success') })" class="hidden"></div>
    @endif
    @if (session('error'))
        <div x-init="$nextTick(() => { Alpine.store('toast').add(@js(session('error')), 'error') })" class="hidden"></div>
    @endif

    <div class="flex h-full min-h-screen bg-surface dark:bg-surface-dark">
        {{-- Mobile overlay --}}
        <div
            x-show="$store.app.mobileSidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="$store.app.closeMobileSidebar()"
        ></div>

        {{-- Sidebar --}}
        @include('layouts.partials.sidebar')

        {{-- Main content --}}
        <div
            class="flex flex-1 flex-col transition-all duration-300"
            :class="$store.app.sidebarCollapsed ? 'lg:ml-[72px]' : 'lg:ml-64'"
        >
            @include('layouts.partials.topbar')

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
        document.addEventListener('alpine:initialized', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
