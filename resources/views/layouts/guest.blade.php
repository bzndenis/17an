<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', '17an') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="h-full font-sans antialiased">
    <div class="flex min-h-full">
        {{-- Left panel --}}
        <div class="hidden w-1/2 flex-col justify-between bg-secondary p-12 lg:flex">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary text-xl font-bold text-white">17</div>
                    <span class="text-2xl font-bold text-white">17an</span>
                </div>
                <h1 class="mt-12 text-4xl font-bold leading-tight text-white">
                    Dashboard Kompetisi<br>
                    <span class="text-primary">Modern & Profesional</span>
                </h1>
                <p class="mt-4 max-w-md text-slate-400">
                    Kelola peserta, lomba, bracket, pertandingan, dan peringkat dalam satu platform terpadu.
                </p>
            </div>
            <p class="text-sm text-slate-500">&copy; {{ date('Y') }} 17an Competition Dashboard</p>
        </div>

        {{-- Right panel --}}
        <div class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-16 bg-surface dark:bg-surface-dark">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-sm font-bold text-white">17</div>
                    <span class="text-xl font-bold text-secondary dark:text-white">17an</span>
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
    <script>document.addEventListener('DOMContentLoaded', () => { if (typeof lucide !== 'undefined') lucide.createIcons(); });</script>
</body>
</html>
